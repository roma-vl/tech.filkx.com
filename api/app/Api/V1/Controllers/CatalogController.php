<?php

namespace App\Api\V1\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['brand', 'categories', 'variants.stocks'])
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
        $product = Product::with(['brand', 'categories', 'variants.stocks'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Increment view count
        $product->increment('views_count');

        return self::successfulResponseWithData($product);
    }
}
