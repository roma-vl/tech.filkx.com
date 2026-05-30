<?php

namespace App\Api\V1\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function queryActive(): Builder
    {
        return Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue',
        ])
            ->where('status', 'active');
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->queryActive()
            ->where('slug', $slug)
            ->first();
    }

    public function getHotDeals(int $limit = 8): Collection
    {
        return Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue',
        ])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('is_hot', true)
                    ->orWhereHas('variants', function ($varQ) {
                        $varQ->whereNotNull('old_price')
                            ->whereRaw('old_price > price');
                    });
            })
            ->get();
    }

    public function getRecommended(int $limit = 8): Collection
    {
        return Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue',
        ])
            ->where('status', 'active')
            ->where('is_recommended', true)
            ->take($limit)
            ->get();
    }

    public function getRandomFallback(array $excludeIds, int $limit): Collection
    {
        return Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'attributeValues.attribute',
            'attributeValues.attributeValue',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue',
        ])
            ->where('status', 'active')
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }
}
