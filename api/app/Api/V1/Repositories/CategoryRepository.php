<?php

namespace App\Api\V1\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    public function getParentCategoriesWithChildren(): Collection
    {
        return Category::with('children.children')
            ->whereNull('parent_id')
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
     * Resolves a category slug to its id plus its direct children's ids - the same
     * category-scope resolution catalog product listing/filtering uses. Returns an
     * empty array when the slug doesn't match any category.
     */
    public function resolveCategoryIdsBySlug(string $slug): array
    {
        $category = $this->findBySlug($slug);
        if (! $category) {
            return [];
        }

        return array_merge([$category->id], $category->children()->pluck('id')->toArray());
    }
}
