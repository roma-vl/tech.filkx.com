<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogTag;
use Illuminate\Support\Str;

class GenerateUniqueBlogTagSlugAction
{
    public function execute(string $source, ?int $excludeId = null): string
    {
        $slug = Str::slug($source);

        if (! $slug) {
            $slug = 'blog-tag-'.time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (
            BlogTag::where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
