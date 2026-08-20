<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogCategory;
use Illuminate\Support\Str;

class GenerateUniqueBlogCategorySlugAction
{
    public function execute(string $source, ?int $excludeId = null): string
    {
        $slug = Str::slug($source);

        if (! $slug) {
            $slug = 'blog-category-'.time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (
            BlogCategory::where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
