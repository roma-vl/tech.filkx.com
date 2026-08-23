<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\CategoryRepository;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Services\Catalog\ProductAttributeFacetCodec;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetCatalogFiltersAction
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductAttributeFacetCodec $facetCodec,
    ) {}

    public function execute(?string $categorySlug = null): array
    {
        $categoryIds = $categorySlug ? $this->categoryRepository->resolveCategoryIdsBySlug($categorySlug) : [];

        try {
            $price = $this->priceRangeViaMeilisearch($categoryIds);
        } catch (\Throwable $e) {
            // Meilisearch is the primary facet source; SQL is kept as a resilience
            // fallback so a Meilisearch outage degrades filter accuracy, not the
            // whole catalog page - matches the existing search-keyword fallback
            // pattern in ListProductsAction.
            Log::error('Meilisearch facet query failed, falling back to SQL price aggregate: '.$e->getMessage());
            $price = $this->priceRangeViaSql($categoryIds);
        }

        if (empty($categoryIds)) {
            // Unscoped (no category) attribute listing has never been "what's
            // actually assigned to an active product" - it's always been every
            // attribute value that exists at all (see GetCatalogFiltersActionTest's
            // "no products exist" case). That's not a product-search question, so
            // it's never Meilisearch-backed, category-scoped or not.
            return ['price' => $price, 'attributes' => $this->attributesUnscoped()];
        }

        try {
            $attributes = $this->attributesScopedToCategoryViaMeilisearch($categoryIds);
        } catch (\Throwable $e) {
            Log::error('Meilisearch facet query failed, falling back to SQL attribute facets: '.$e->getMessage());
            $attributes = $this->attributesScopedToCategoryViaSql($categoryIds);
        }

        return ['price' => $price, 'attributes' => $attributes];
    }

    private function priceRangeViaMeilisearch(array $categoryIds): array
    {
        $filters = ['status = "active"'];
        if (! empty($categoryIds)) {
            $filters[] = 'category_ids IN ['.implode(',', $categoryIds).']';
        }

        $results = Product::search('')
            ->options([
                'filter' => $filters,
                'facets' => ['price_min', 'price_max'],
            ])
            ->take(0)
            ->raw();

        $facetStats = $results['facetStats'] ?? [];

        return [
            'min' => isset($facetStats['price_min']['min']) ? floor($facetStats['price_min']['min']) : 0,
            'max' => isset($facetStats['price_max']['max']) ? ceil($facetStats['price_max']['max']) : 200000,
        ];
    }

    private function priceRangeViaSql(array $categoryIds): array
    {
        $priceQuery = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.status', 'active');

        if (! empty($categoryIds)) {
            $priceQuery->whereExists(function ($query) use ($categoryIds) {
                $query->select(DB::raw(1))
                    ->from('product_category')
                    ->whereColumn('product_category.product_id', 'products.id')
                    ->whereIn('product_category.category_id', $categoryIds);
            });
        }

        $priceStats = $priceQuery->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        return [
            'min' => $priceStats->min_price ? floor($priceStats->min_price) : 0,
            'max' => $priceStats->max_price ? ceil($priceStats->max_price) : 200000,
        ];
    }

    private function attributesScopedToCategoryViaMeilisearch(array $categoryIds): Collection
    {
        $results = Product::search('')
            ->options([
                'filter' => [
                    'status = "active"',
                    'category_ids IN ['.implode(',', $categoryIds).']',
                ],
                'facets' => ['attributes'],
            ])
            ->take(0)
            ->raw();

        $tokens = array_keys($results['facetDistribution']['attributes'] ?? []);

        $valueIds = collect($tokens)
            ->map(fn (string $token) => $this->facetCodec->decodeAttributeValueId($token))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($valueIds)) {
            return collect();
        }

        return $this->hydrateAttributeValues(AttributeValue::with('attribute')->whereIn('id', $valueIds)->get());
    }

    private function attributesScopedToCategoryViaSql(array $categoryIds): Collection
    {
        $valuesInScope = function ($query) use ($categoryIds) {
            $query->whereHas('productAttributeValues.product.categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        };

        return Attribute::with(['values' => $valuesInScope])
            ->whereHas('values', $valuesInScope)
            ->get()
            ->map(fn (Attribute $attribute) => $this->mapAttributePayload($attribute, $attribute->values));
    }

    /**
     * Every attribute value that exists, regardless of whether any active
     * product currently references it - see the comment in execute().
     */
    private function attributesUnscoped(): Collection
    {
        return Attribute::with('values')
            ->whereHas('values')
            ->get()
            ->map(fn (Attribute $attribute) => $this->mapAttributePayload($attribute, $attribute->values));
    }

    private function hydrateAttributeValues(Collection $values): Collection
    {
        return $values
            ->groupBy('attribute_id')
            ->map(fn (Collection $valuesForAttribute) => $this->mapAttributePayload(
                $valuesForAttribute->first()->attribute,
                $valuesForAttribute->sortBy('id')->values()
            ))
            ->sortBy('id')
            ->values();
    }

    private function mapAttributePayload(Attribute $attribute, Collection $values): array
    {
        return [
            'id' => $attribute->id,
            'code' => $attribute->code,
            'name' => $attribute->name,
            'type' => $attribute->type,
            'values' => $values->map(fn (AttributeValue $value) => [
                'id' => $value->id,
                'value' => $value->value,
            ])->values(),
        ];
    }
}
