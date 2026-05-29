<?php

namespace App\Api\V1\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends BaseApiController
{
    public function categories(): JsonResponse
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        return self::successfulResponseWithData($categories);
    }

    public function brands(): JsonResponse
    {
        $brands = Brand::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('name')->get();

        return self::successfulResponseWithData($brands);
    }

    public function filters(): JsonResponse
    {
        // 1. Min/Max price of all active products
        $priceStats = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.status', 'active')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        // 2. Fetch all attributes with their values that have product assignments
        $attributes = Attribute::with(['values'])
            ->whereHas('values')
            ->get()
            ->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'code' => $attr->code,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'values' => $attr->values->map(function ($val) {
                        return [
                            'id' => $val->id,
                            'value' => $val->value,
                        ];
                    })
                ];
            });

        return self::successfulResponseWithData([
            'price' => [
                'min' => $priceStats->min_price ? floor($priceStats->min_price) : 0,
                'max' => $priceStats->max_price ? ceil($priceStats->max_price) : 200000,
            ],
            'attributes' => $attributes
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue'
        ])
        ->where('status', 'active');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name->uk', 'like', "%{$search}%")
                  ->orWhere('name->en', 'like', "%{$search}%")
                  ->orWhere('description->uk', 'like', "%{$search}%")
                  ->orWhere('description->en', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                // Get all descendant category IDs
                $categoryIds = [$category->id];
                $categoryIds = array_merge($categoryIds, $category->children()->pluck('id')->toArray());
                
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        // Brand filter
        if ($request->filled('brand')) {
            $brandSlugs = explode(',', $request->input('brand'));
            $query->whereHas('brand', function ($q) use ($brandSlugs) {
                $q->whereIn('slug', $brandSlugs);
            });
        }

        // Price filter
        if ($request->filled('price_from') || $request->filled('price_to')) {
            $priceFrom = $request->input('price_from');
            $priceTo = $request->input('price_to');

            $query->whereHas('variants', function ($q) use ($priceFrom, $priceTo) {
                if ($priceFrom !== null) {
                    $q->where('price', '>=', $priceFrom);
                }
                if ($priceTo !== null) {
                    $q->where('price', '<=', $priceTo);
                }
            });
        }

        // Discounts filter
        if ($request->boolean('discounts')) {
            $query->whereHas('variants', function ($q) {
                $q->whereNotNull('old_price');
            });
        }

        // In Stock filter
        if ($request->boolean('in_stock')) {
            $query->whereHas('variants.stocks', function ($q) {
                $q->whereRaw('quantity > reserved');
            });
        }

        // EAV Attributes filter
        if ($request->filled('attrs') && is_array($request->input('attrs'))) {
            foreach ($request->input('attrs') as $attrCode => $attrValues) {
                if (empty($attrValues)) {
                    continue;
                }
                
                if (is_string($attrValues)) {
                    $attrValues = explode(',', $attrValues);
                }
                
                $query->where(function ($q) use ($attrCode, $attrValues) {
                    // Check at product level
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
                    // OR check at variant level
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

        // Sorting
        $sortBy = $request->input('sort_by', 'popularity');
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

        $products = $query->paginate(self::PER_PAGE);

        return self::successfulResponseWithData($products);
    }

    public function product(string $slug): JsonResponse
    {
        $product = Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue'
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        // Increment view count
        $product->increment('views_count');

        return self::successfulResponseWithData($product);
    }

    public function homeData(): JsonResponse
    {
        // 1. Popular categories (8 categories with the most active products)
        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])
        ->orderBy('products_count', 'desc')
        ->take(8)
        ->get();

        // 2. Flash Deals (is_hot = true OR has variants with old_price > price)
        $flashDeals = Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue'
        ])
        ->where('status', 'active')
        ->where(function ($q) {
            $q->where('is_hot', true)
              ->orWhereHas('variants', function ($varQ) {
                  $varQ->whereNotNull('old_price')
                       ->whereRaw('old_price > price');
              });
        })
        ->get()
        ->sortByDesc(function ($product) {
            if ($product->is_hot) {
                return 999;
            }
            $maxDiscountPct = 0;
            foreach ($product->variants as $variant) {
                if ($variant->old_price && $variant->old_price > $variant->price) {
                    $pct = ($variant->old_price - $variant->price) / $variant->old_price;
                    if ($pct > $maxDiscountPct) {
                        $maxDiscountPct = $pct;
                    }
                }
            }
            return $maxDiscountPct;
        })
        ->take(8)
        ->values();

        // 3. Recommended products (is_recommended = true or random fallback)
        $recommended = Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue'
        ])
        ->where('status', 'active')
        ->where('is_recommended', true)
        ->take(8)
        ->get();

        if ($recommended->count() < 8) {
            $needed = 8 - $recommended->count();
            $excludeIds = $recommended->pluck('id')->toArray();
            $random = Product::with([
                'brand',
                'categories',
                'variants.stocks',
                'attributeValues.attribute',
                'attributeValues.attributeValue',
                'variants.attributeValues.attribute',
                'variants.attributeValues.attributeValue'
            ])
            ->where('status', 'active')
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->take($needed)
            ->get();
            
            $recommended = $recommended->concat($random);
        }

        return self::successfulResponseWithData([
            'categories' => $categories,
            'flash_deals' => $flashDeals,
            'recommended' => $recommended->values()
        ]);
    }
}
