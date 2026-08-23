<?php

namespace App\Api\Admin\Actions\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchAdminProductsAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters)->with([
            'brand',
            'categories',
            'variants.stocks',
            'variants.attributeValues.attribute',
            'variants.attributeValues.attributeValue',
        ]);

        $this->applySort($query, $filters['sort'] ?? 'name-asc');

        return $query->paginate(
            (int) ($filters['perPage'] ?? 15),
            ['*'],
            'page',
            (int) ($filters['page'] ?? 1)
        );
    }

    /**
     * Ids of every product matching the filters, ignoring sort/pagination -
     * backs the "select all N matching the filter" bulk-action shortcut,
     * which only ever needs the id list, not full resource-shaped rows.
     *
     * @return array<int, int>
     */
    public function executeIds(array $filters): array
    {
        return $this->buildFilteredQuery($filters)->pluck('products.id')->all();
    }

    private function buildFilteredQuery(array $filters): Builder
    {
        $query = Product::query();

        if (! empty($filters['search'])) {
            $ids = $this->searchProductIds($filters['search']);
            $query->whereIn('id', empty($ids) ? [-1] : $ids);
        }

        if (! empty($filters['categoryId'])) {
            $categoryId = (int) $filters['categoryId'];
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $categoryId));
        }

        if (! empty($filters['brandId'])) {
            $query->where('brand_id', (int) $filters['brandId']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (filter_var($filters['hot'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('is_hot', true);
        }

        if (filter_var($filters['recommended'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('is_recommended', true);
        }

        if (! empty($filters['hasImage'])) {
            $this->applyHasImageFilter($query, $filters['hasImage']);
        }

        if (! empty($filters['stock'])) {
            $hasPositiveStock = fn (Builder $q) => $q->where('quantity', '>', 0);
            if ($filters['stock'] === 'inStock') {
                $query->whereHas('variants.stocks', $hasPositiveStock);
            } else {
                $query->whereDoesntHave('variants.stocks', $hasPositiveStock);
            }
        }

        return $query;
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'name-desc' => $query->orderByRaw("name->>'uk' DESC"),
            'price-asc' => $query->withMin('variants', 'price')->orderBy('variants_min_price', 'asc'),
            'price-desc' => $query->withMax('variants', 'price')->orderBy('variants_max_price', 'desc'),
            'stock-desc' => $query->addSelect(['total_stock' => $this->totalStockSubquery()])->orderBy('total_stock', 'desc'),
            'stock-asc' => $query->addSelect(['total_stock' => $this->totalStockSubquery()])->orderBy('total_stock', 'asc'),
            default => $query->orderByRaw("name->>'uk' ASC"),
        };
    }

    private function totalStockSubquery(): QueryBuilder
    {
        return DB::table('stocks')
            ->selectRaw('COALESCE(SUM(stocks.quantity), 0)')
            ->join('product_variants', 'product_variants.id', '=', 'stocks.variant_id')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->whereNull('product_variants.deleted_at');
    }

    /**
     * "Has photo" means at least one (non-deleted) variant has a real image in
     * its `dimensions` JSON - either the current `images` array or the legacy
     * single `image` key (see AdminProductResource for the same two sources).
     * whereJsonLength/where('dimensions->image', ...) compile to the right
     * native JSON syntax per database driver (jsonb ops on Postgres in
     * production, json_extract on SQLite in tests) instead of raw SQL tied to
     * one of them.
     */
    private function applyHasImageFilter(Builder $query, string $value): void
    {
        $hasRealImage = function (Builder $q) {
            $q->where(function (Builder $inner) {
                $inner->whereJsonLength('dimensions->images', '>', 0)
                    ->orWhere('dimensions->image', '!=', '');
            });
        };

        if ($value === 'with') {
            $query->whereHas('variants', $hasRealImage);
        } else {
            $query->whereDoesntHave('variants', $hasRealImage);
        }
    }

    /**
     * Meilisearch (via Scout) covers the name fields; SKUs aren't indexed
     * there, so a plain SQL match on variant SKUs is merged in alongside it.
     * Falls back to a SQL name match if Meilisearch itself is unreachable,
     * matching the resilience pattern in ListProductsAction.
     *
     * @return array<int, int>
     */
    private function searchProductIds(string $search): array
    {
        try {
            $ids = Product::search($search)->keys()->all();
        } catch (\Throwable $e) {
            Log::error('Meilisearch admin product search failed, falling back to SQL name search: '.$e->getMessage());
            $ids = Product::where(function (Builder $q) use ($search) {
                $q->where('name->uk', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%");
            })->pluck('id')->all();
        }

        $skuIds = Product::whereHas('variants', fn (Builder $q) => $q->where('sku', 'like', "%{$search}%"))
            ->pluck('id')->all();

        return array_values(array_unique(array_merge($ids, $skuIds)));
    }
}
