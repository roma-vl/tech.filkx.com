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
}
