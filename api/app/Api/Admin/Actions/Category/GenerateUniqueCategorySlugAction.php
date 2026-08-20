<?php

namespace App\Api\Admin\Actions\Category;

use App\Models\Category;
use Illuminate\Support\Str;

class GenerateUniqueCategorySlugAction
{
    public function execute(string $source, ?int $excludeId = null): string
    {
        $slug = Str::slug($source);

        if (! $slug) {
            $slug = 'category-'.time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (
            Category::where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
