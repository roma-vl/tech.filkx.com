<?php

namespace App\Api\V1\Repositories;

use App\Models\PromoPage;
use Illuminate\Database\Eloquent\Collection;

class PromoPageRepository implements PromoPageRepositoryInterface
{
    public function all(): Collection
    {
        return PromoPage::withCount('products')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function find(int $id): ?PromoPage
    {
        return PromoPage::with('products')->find($id);
    }

    public function findActiveBySlugWithProducts(string $slug): ?PromoPage
    {
        return PromoPage::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'products' => fn ($query) => $query
                    ->where('status', 'active')
                    ->with([
                        'brand',
                        'categories',
                        'variants.stocks',
                        'attributeValues.attribute',
                        'attributeValues.attributeValue',
                        'variants.attributeValues.attribute',
                        'variants.attributeValues.attributeValue',
                    ])
                    ->withCount('approvedReviews')
                    ->withAvg('approvedReviews', 'rating'),
            ])
            ->first();
    }

    public function create(array $data): PromoPage
    {
        return PromoPage::create($data);
    }

    public function update(PromoPage $promoPage, array $data): PromoPage
    {
        $promoPage->update($data);

        return $promoPage;
    }

    public function delete(PromoPage $promoPage): bool
    {
        return (bool) $promoPage->delete();
    }

    public function syncProducts(PromoPage $promoPage, array $productIds): void
    {
        $sync = [];
        foreach (array_values($productIds) as $index => $productId) {
            $sync[$productId] = ['sort_order' => $index];
        }

        $promoPage->products()->sync($sync);
    }
}
