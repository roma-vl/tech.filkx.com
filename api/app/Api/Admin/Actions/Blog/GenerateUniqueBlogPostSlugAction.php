<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogPost;
use Illuminate\Support\Str;

class GenerateUniqueBlogPostSlugAction
{
    public function execute(string $source, ?int $excludeId = null): string
    {
        $slug = Str::slug($source);

        if (! $slug) {
            $slug = 'post-'.time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (
            BlogPost::withTrashed()
                ->where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
