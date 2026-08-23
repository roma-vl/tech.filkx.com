<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\BrandRepository;
use App\Api\V1\Repositories\CategoryRepository;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Services\Catalog\ProductAttributeFacetCodec;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder as ScoutBuilder;

class ListProductsAction
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CategoryRepository $categoryRepository,
        protected BrandRepository $brandRepository,
        protected ProductAttributeFacetCodec $facetCodec,
    ) {}

    public function execute(array $filters): LengthAwarePaginator
    {
        try {
            return $this->executeViaMeilisearch($filters);
        } catch (\Throwable $e) {
            // Meilisearch is the primary filtering backend; SQL is kept as a
            // resilience fallback so a Meilisearch outage degrades the catalog,
            // not takes it down entirely.
            Log::error('Meilisearch product query failed, falling back to SQL filtering: '.$e->getMessage());

            return $this->executeViaSql($filters);
        }
    }

    private function executeViaMeilisearch(array $filters): LengthAwarePaginator
    {
        $search = Product::search($filters['search'] ?? '')
            ->options(['filter' => $this->buildFilterClauses($filters)])
            ->query(function (EloquentBuilder $query) {
                $query->with([
                    'brand',
                    'categories',
                    'variants.stocks',
                    'attributeValues.attribute',
                    'attributeValues.attributeValue',
                    'variants.attributeValues.attribute',
                    'variants.attributeValues.attributeValue',
                ])
                    ->withCount('approvedReviews')
                    ->withAvg('approvedReviews', 'rating');
            });

        $this->applySort($search, $filters['sort_by'] ?? 'popularity');

        // 24 divides evenly into the catalog grid's 4- and 5-column breakpoints, and keeps
        // a page tall enough that the sticky filter sidebar doesn't dwarf a short results list.
        return $search->paginate(24);
    }

    private function applySort(ScoutBuilder $search, string $sortBy): void
    {
        match ($sortBy) {
            'newest' => $search->orderBy('created_at', 'desc'),
            'price-asc' => $search->orderBy('price_min', 'asc'),
            'price-desc' => $search->orderBy('price_min', 'desc'),
            default => $search->orderBy('views_count', 'desc'),
        };
    }

    private function buildFilterClauses(array $filters): array
    {
        $clauses = ['status = "active"'];

        if (! empty($filters['category'])) {
            $categoryIds = $this->categoryRepository->resolveCategoryIdsBySlug($filters['category']);
            $clauses[] = empty($categoryIds)
                ? 'category_ids = -1'
                : 'category_ids IN ['.implode(',', $categoryIds).']';
        }

        if (! empty($filters['brand'])) {
            $brandSlugs = is_string($filters['brand']) ? explode(',', $filters['brand']) : $filters['brand'];
            $brandIds = $this->brandRepository->findIdsBySlugs($brandSlugs);
            $clauses[] = empty($brandIds)
                ? 'brand_id = -1'
                : 'brand_id IN ['.implode(',', $brandIds).']';
        }

        if (isset($filters['price_from']) || isset($filters['price_to'])) {
            $clauses[] = $this->priceRangeClause(
                isset($filters['price_from']) ? (float) $filters['price_from'] : null,
                isset($filters['price_to']) ? (float) $filters['price_to'] : null,
            );
        }

        if (filter_var($filters['discounts'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $clauses[] = 'has_discount = true';
        }

        if (filter_var($filters['in_stock'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $clauses[] = 'in_stock = true';
        }

        if (! empty($filters['attrs']) && is_array($filters['attrs'])) {
            foreach ($filters['attrs'] as $attrCode => $attrValues) {
                if (empty($attrValues)) {
                    continue;
                }

                $attrValues = is_string($attrValues) ? explode(',', $attrValues) : $attrValues;
                $clauses[] = $this->attributeFilterGroup($attrCode, $attrValues);
            }
        }

        return $clauses;
    }

    private function priceRangeClause(?float $from, ?float $to): string
    {
        if ($from !== null && $to !== null) {
            return "variant_prices {$from} TO {$to}";
        }

        return $from !== null ? "variant_prices >= {$from}" : "variant_prices <= {$to}";
    }

    /**
     * One OR-group of Meilisearch filter tokens for a single attribute code -
     * matches if the product has any of the requested values, either as a real
     * AttributeValue (any locale) or as a free-text custom_value.
     *
     * @return array<int, string>
     */
    private function attributeFilterGroup(string $attrCode, array $values): array
    {
        $attributeValueIds = AttributeValue::whereHas('attribute', fn (EloquentBuilder $q) => $q->where('code', $attrCode))
            ->where(function (EloquentBuilder $q) use ($values) {
                foreach ($values as $value) {
                    $q->orWhere('value->uk', 'like', $value)
                        ->orWhere('value->en', 'like', $value)
                        ->orWhere('value', 'like', $value);
                }
            })
            ->pluck('id');

        $tokens = $attributeValueIds
            ->map(fn (int $id) => $this->facetCodec->encodeAttributeValue($attrCode, $id))
            ->values()
            ->all();

        foreach ($values as $value) {
            $tokens[] = $this->facetCodec->encodeCustomValue($attrCode, $value);
        }

        return array_map(fn (string $token) => 'attributes = "'.$token.'"', $tokens);
    }

    private function executeViaSql(array $filters): LengthAwarePaginator
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
                Log::error('Meilisearch query failed, falling back to SQL search: '.$e->getMessage());
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
            $categoryIds = $this->categoryRepository->resolveCategoryIdsBySlug($filters['category']);
            if (! empty($categoryIds)) {
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
                ->addSelect('products.*')
                ->selectRaw('MIN(product_variants.price) as min_price')
                ->groupBy('products.id')
                ->orderBy('min_price', 'asc');
        } elseif ($sortBy === 'price-desc') {
            $query->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                ->addSelect('products.*')
                ->selectRaw('MIN(product_variants.price) as min_price')
                ->groupBy('products.id')
                ->orderBy('min_price', 'desc');
        } else {
            $query->orderBy('products.views_count', 'desc');
        }

        // 24 divides evenly into the catalog grid's 4- and 5-column breakpoints, and keeps
        // a page tall enough that the sticky filter sidebar doesn't dwarf a short results list.
        return $query->paginate(24);
    }
}
