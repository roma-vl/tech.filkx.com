<?php

namespace App\Api\Admin\Actions\Page;

use App\Models\Page;
use Illuminate\Support\Str;

class GenerateUniquePageSlugAction
{
    public function execute(string $source, ?int $excludeId = null): string
    {
        $slug = Str::slug($source);

        if (! $slug) {
            $slug = 'page-'.time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (
            Page::where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
