<?php

namespace App\Api\V1\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(): Collection
    {
        return Product::with([
            'brand',
            'categories',
            'variants.stocks',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue',
        ])->get();
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function slugExists(string $slug): bool
    {
        return Product::where('slug', $slug)->exists();
    }

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
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->where('status', 'active');
    }

    public function findBySlug(string $slug): ?Product
    {
        $query = $this->queryActive();

        if (is_numeric($slug)) {
            $product = (clone $query)->where('id', (int) $slug)->first();
            if ($product) {
                return $product;
            }
        }

        return $query->where('slug', $slug)->first();
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
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
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
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
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
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->where('status', 'active')
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }
}
