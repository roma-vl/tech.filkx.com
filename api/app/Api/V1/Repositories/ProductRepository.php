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
        // Both products and product_variants are soft-deleted so an order
        // placed against this product keeps order_items.variant_id pointing
        // at a real (if hidden) row instead of losing the reference - but
        // the products -> product_variants foreign key's cascadeOnDelete is
        // a hard-delete trigger, so it never fires for a soft delete. The
        // variants have to be soft-deleted explicitly here.
        $product->variants()->delete();

        return (bool) $product->delete();
    }

    public function slugExists(string $slug): bool
    {
        return Product::where('slug', $slug)->exists();
    }

    public function findTrashed(int $id): ?Product
    {
        return Product::onlyTrashed()->find($id);
    }

    public function trashed(): Collection
    {
        return Product::onlyTrashed()
            ->with([
                'brand',
                'categories',
                'variants' => fn ($query) => $query->withTrashed()->with([
                    'stocks',
                    'attributeValues.attribute',
                    'attributeValues.attributeValue',
                ]),
            ])
            ->get();
    }

    public function restore(Product $product): bool
    {
        $product->variants()->withTrashed()->restore();

        return (bool) $product->restore();
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
        return $this->hotDealsQuery()->take($limit)->get();
    }

    /**
     * Same "real deal" criteria as getHotDeals(), but rotated deterministically by
     * $seed (typically the current year+month+day+hour) instead of relying on the
     * database to shuffle. Postgres's grammar for inRandomOrder() ignores the seed
     * argument entirely (only MySqlGrammar::compileRandom() honors it) and always
     * compiles to a plain `ORDER BY RANDOM()`, so a DB-level seeded order would
     * silently reshuffle on every request instead of staying stable for the hour.
     */
    public function getSeededHotDeals(int $limit, int $seed): \Illuminate\Support\Collection
    {
        $ids = $this->hotDealsQuery()->orderBy('id')->pluck('id')->all();

        if (empty($ids)) {
            return new Collection;
        }

        mt_srand($seed);
        shuffle($ids);
        mt_srand();

        $selectedIds = array_slice($ids, 0, $limit);

        return $this->hotDealsQuery()
            ->whereIn('id', $selectedIds)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $selectedIds, true))
            ->values();
    }

    private function hotDealsQuery(): Builder
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
            ->where(function (Builder $q) {
                $q->where('is_hot', true)
                    ->orWhereHas('variants', function ($varQ) {
                        $varQ->whereNotNull('old_price')
                            ->whereRaw('old_price > price');
                    });
            });
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

    public function getRelated(Product $product, int $limit = 8): Collection
    {
        // Query fresh rather than reading $product->categories: Scout's Searchable trait
        // indexes the model synchronously on create/save, which eager-loads (and caches
        // empty) this relation before a caller has had a chance to attach categories to a
        // newly created product.
        $categoryIds = $product->categories()->pluck('categories.id');

        $related = Product::with([
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
            ->where('id', '!=', $product->id)
            ->when(
                $categoryIds->isNotEmpty(),
                fn (Builder $q) => $q->whereHas(
                    'categories',
                    fn (Builder $catQuery) => $catQuery->whereIn('categories.id', $categoryIds)
                )
            )
            ->inRandomOrder()
            ->take($limit)
            ->get();

        // Same category didn't have enough products - top up with random
        // active products rather than showing a half-empty section.
        if ($related->count() < $limit) {
            $excludeIds = $related->pluck('id')->push($product->id)->all();
            $related = $related->concat(
                (array) $this->getRandomFallback($excludeIds, $limit - $related->count())
            );
        }

        return $related;
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
