<?php

namespace App\Api\Admin\Actions\PromoPage;

use App\Models\PromoPage;
use Illuminate\Support\Str;

class GenerateUniquePromoPageSlugAction
{
    public function execute(string $source, ?int $excludeId = null): string
    {
        $slug = Str::slug($source);

        if (! $slug) {
            $slug = 'promo-'.time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (
            PromoPage::where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
