<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\CategoryRepository;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Http\Request;

class GetHomeDataAction
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    public function execute(Request $request): array
    {
        // 1. Popular categories (8 categories with most active products)
        $categories = $this->categoryRepository->getPopularCategories(8);

        // 2. Flash Deals (is_hot = true or highest variant discount percentage)
        $flashDealsRaw = $this->productRepository->getHotDeals();
        $flashDeals = $flashDealsRaw->sortByDesc(function ($product) {
            if ($product->is_hot) {
                return 9.99; // Top priority
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

        // 3. Smart Recommendations Algorithm
        $recommendedIds = collect();
        
        // Parse wishlist and viewed items sent by the frontend
        $wishlistIds = array_filter(explode(',', $request->query('wishlist_ids', '')));
        $viewedIds = array_filter(explode(',', $request->query('viewed_ids', '')));
        
        $seedIds = array_merge($wishlistIds, $viewedIds);

        if (!empty($seedIds)) {
            // Find categories and brands of viewed/wishlisted items
            $seedProducts = Product::whereIn('id', $seedIds)->with('categories')->get();
            $seedCategoryIds = $seedProducts->flatMap(fn($p) => $p->categories->pluck('id'))->unique();
            $seedBrandIds = $seedProducts->pluck('brand_id')->filter()->unique();

            if ($seedCategoryIds->isNotEmpty() || $seedBrandIds->isNotEmpty()) {
                // Find active products in the same categories/brands, excluding the seed products
                $relatedProducts = Product::with([
                    'brand',
                    'categories',
                    'variants.stocks',
                    'attributeValues.attribute',
                    'attributeValues.attributeValue',
                    'variants.attributeValues.attribute',
                    'variants.attributeValues.attributeValue'
                ])
                ->where('status', 'active')
                ->whereNotIn('id', $seedIds)
                ->where(function ($query) use ($seedCategoryIds, $seedBrandIds) {
                    if ($seedCategoryIds->isNotEmpty()) {
                        $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $seedCategoryIds));
                    }
                    if ($seedBrandIds->isNotEmpty()) {
                        $query->orWhereIn('brand_id', $seedBrandIds);
                    }
                })
                ->orderBy('views_count', 'desc')
                ->take(8)
                ->get();

                $recommendedIds = $relatedProducts->pluck('id');
            }
        }

        // Fetch products marked as is_recommended = true
        $explicitPromo = $this->productRepository->getRecommended(8);
        $explicitPromoIds = $explicitPromo->pluck('id');

        // Combine smart recommendations with explicit promotions
        $combinedIds = $recommendedIds->merge($explicitPromoIds)->unique()->take(8)->toArray();

        // Load complete relation datasets for the selected IDs
        $recommended = Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue'
        ])
        ->whereIn('id', $combinedIds)
        ->where('status', 'active')
        ->get()
        // Maintain the order of IDs
        ->sortBy(fn($product) => array_search($product->id, $combinedIds));

        // Fallback to random popular active items if less than 8 items match
        if ($recommended->count() < 8) {
            $needed = 8 - $recommended->count();
            $excludeIds = array_merge($combinedIds, $seedIds);
            
            $fallback = $this->productRepository->getRandomFallback($excludeIds, $needed);
            $recommended = $recommended->concat($fallback);
        }

        return [
            'categories' => $categories,
            'flash_deals' => $flashDeals,
            'recommended' => $recommended->values()
        ];
    }
}
