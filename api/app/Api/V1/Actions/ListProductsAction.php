<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\CategoryRepository;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CategoryRepository $categoryRepository
    ) {}

    public function execute(array $filters): LengthAwarePaginator
    {
        $query = $this->productRepository->queryActive();

        // 1. Meilisearch Integration for search keyword
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            try {
                // Perform Meilisearch query via Scout
                $meiliProductIds = Product::search($search)
                    ->where('status', 'active')
                    ->keys()
                    ->toArray();

                if (! empty($meiliProductIds)) {
                    $query->whereIn('products.id', $meiliProductIds);
                } else {
                    // If no match found in Meilisearch, force empty result set
                    $query->whereRaw('1 = 0');
                }
            } catch (\Throwable $e) {
                // Fallback to SQL search if Meilisearch service is down
                logger()->error('Meilisearch query failed, falling back to SQL search: '.$e->getMessage());
                $query->where(function ($q) use ($search) {
                    $q->where('name->uk', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('description->uk', 'like', "%{$search}%")
                        ->orWhere('description->en', 'like', "%{$search}%");
                });
            }
        }

        // 2. Category filter
        if (! empty($filters['category'])) {
            $categorySlug = $filters['category'];
            $category = $this->categoryRepository->findBySlug($categorySlug);
            if ($category) {
                $categoryIds = [$category->id];
                $categoryIds = array_merge($categoryIds, $category->children()->pluck('id')->toArray());

                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        // 3. Brand filter
        if (! empty($filters['brand'])) {
            $brandSlugs = is_string($filters['brand']) ? explode(',', $filters['brand']) : $filters['brand'];
            $query->whereHas('brand', function ($q) use ($brandSlugs) {
                $q->whereIn('slug', $brandSlugs);
            });
        }

        // 4. Price range filter
        if (isset($filters['price_from']) || isset($filters['price_to'])) {
            $priceFrom = $filters['price_from'] ?? null;
            $priceTo = $filters['price_to'] ?? null;

            $query->whereHas('variants', function ($q) use ($priceFrom, $priceTo) {
                if ($priceFrom !== null) {
                    $q->where('price', '>=', $priceFrom);
                }
                if ($priceTo !== null) {
                    $q->where('price', '<=', $priceTo);
                }
            });
        }

        // 5. Discount flag filter
        if (filter_var($filters['discounts'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('variants', function ($q) {
                $q->whereNotNull('old_price');
            });
        }

        // 6. In-stock flag filter
        if (filter_var($filters['in_stock'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('variants.stocks', function ($q) {
                $q->whereRaw('quantity > reserved');
            });
        }

        // 7. EAV Specs/Attributes filter
        if (! empty($filters['attrs']) && is_array($filters['attrs'])) {
            foreach ($filters['attrs'] as $attrCode => $attrValues) {
                if (empty($attrValues)) {
                    continue;
                }

                if (is_string($attrValues)) {
                    $attrValues = explode(',', $attrValues);
                }

                $query->where(function ($q) use ($attrCode, $attrValues) {
                    $q->whereHas('attributeValues', function ($attrValQ) use ($attrCode, $attrValues) {
                        $attrValQ->whereHas('attribute', function ($attrQ) use ($attrCode) {
                            $attrQ->where('code', $attrCode);
                        })->where(function ($subQ) use ($attrValues) {
                            $subQ->whereHas('attributeValue', function ($valQ) use ($attrValues) {
                                $valQ->where(function ($jsonQ) use ($attrValues) {
                                    foreach ($attrValues as $val) {
                                        $jsonQ->orWhere('value->uk', 'like', $val)
                                            ->orWhere('value->en', 'like', $val)
                                            ->orWhere('value', 'like', $val);
                                    }
                                });
                            })->orWhereIn('custom_value', $attrValues);
                        });
                    })
                        ->orWhereHas('variants.attributeValues', function ($attrValQ) use ($attrCode, $attrValues) {
                            $attrValQ->whereHas('attribute', function ($attrQ) use ($attrCode) {
                                $attrQ->where('code', $attrCode);
                            })->where(function ($subQ) use ($attrValues) {
                                $subQ->whereHas('attributeValue', function ($valQ) use ($attrValues) {
                                    $valQ->where(function ($jsonQ) use ($attrValues) {
                                        foreach ($attrValues as $val) {
                                            $jsonQ->orWhere('value->uk', 'like', $val)
                                                ->orWhere('value->en', 'like', $val)
                                                ->orWhere('value', 'like', $val);
                                        }
                                    });
                                })->orWhereIn('custom_value', $attrValues);
                            });
                        });
                });
            }
        }

        // 8. Sorting
        $sortBy = $filters['sort_by'] ?? 'popularity';
        if ($sortBy === 'newest') {
            $query->orderBy('products.created_at', 'desc');
        } elseif ($sortBy === 'price-asc') {
            $query->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->select('products.*')
                ->selectRaw('MIN(product_variants.price) as min_price')
                ->groupBy('products.id')
                ->orderBy('min_price', 'asc');
        } elseif ($sortBy === 'price-desc') {
            $query->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->select('products.*')
                ->selectRaw('MIN(product_variants.price) as min_price')
                ->groupBy('products.id')
                ->orderBy('min_price', 'desc');
        } else {
            $query->orderBy('products.views_count', 'desc');
        }

        return $query->paginate(10);
    }
}
