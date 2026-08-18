<?php

namespace App\Api\V1\Actions\Blog;

use App\Models\BlogCategory;
use Illuminate\Support\Collection;

class ListBlogCategoriesAction
{
    public function execute(): Collection
    {
        return BlogCategory::withCount(['posts' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('order')
            ->get()
            ->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
                'postsCount' => $category->posts_count,
            ]);
    }
}
