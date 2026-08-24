<?php

namespace App\Api\V1\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /**
     * Parent categories for the site nav/mega-menu, with two levels of
     * children - but only branches that actually have an active product
     * somewhere underneath them. A category with no products (directly or
     * via its subcategories) is just dead-end clutter in a menu meant for
     * browsing the catalog, so it's excluded at every level rather than
     * left for the frontend to filter out after the fact.
     */
    public function getParentCategoriesWithChildren(): Collection
    {
        $hasActiveProducts = fn ($query) => $query->whereHas(
            'products',
            fn ($productQuery) => $productQuery->where('status', 'active')
        );

        $nonEmpty = function ($query) use ($hasActiveProducts) {
            $hasActiveProducts($query);
            $query->orWhereHas('children', $hasActiveProducts);
        };

        return Category::whereNull('parent_id')
            ->where(function ($query) use ($hasActiveProducts, $nonEmpty) {
                $hasActiveProducts($query);
                $query->orWhereHas('children', $nonEmpty);
            })
            ->with(['children' => function ($childQuery) use ($nonEmpty, $hasActiveProducts) {
                $childQuery->where($nonEmpty)
                    ->with(['children' => function ($grandchildQuery) use ($hasActiveProducts) {
                        $grandchildQuery->where($hasActiveProducts);
                    }]);
            }])
            ->orderBy('order')
            ->get();
    }

    public function getPopularCategories(int $limit = 8): Collection
    {
        return Category::whereHas('products', function ($q) {
            $q->where('status', 'active');
        })
            ->withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('products_count', 'desc')
            ->take($limit)
            ->get();
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * Resolves a category slug to its id plus every descendant id (children,
     * grandchildren, ...) - the same category-scope resolution catalog product
     * listing/filtering uses. Recursive rather than one level deep so a product
     * tagged only on a leaf category still surfaces when browsing an ancestor
     * further up the tree. Returns an empty array when the slug doesn't match
     * any category.
     */
    public function resolveCategoryIdsBySlug(string $slug): array
    {
        $category = $this->findBySlug($slug);
        if (! $category) {
            return [];
        }

        return array_merge([$category->id], $category->getDescendantIds());
    }
}
