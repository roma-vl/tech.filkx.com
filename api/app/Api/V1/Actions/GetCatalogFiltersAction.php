<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\CategoryRepository;
use App\Models\Attribute;
use Illuminate\Support\Facades\DB;

class GetCatalogFiltersAction
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    public function execute(?string $categorySlug = null): array
    {
        $categoryIds = $categorySlug ? $this->categoryRepository->resolveCategoryIdsBySlug($categorySlug) : [];

        // 1. Min/Max price of active products, scoped to the category (+ its children) when given
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

        // 2. Attributes with values that are actually assigned to products in scope.
        // `product_attribute_values.product_id` is always set (even for variant-level
        // rows - see the migration), so a single hop through it is enough to reach the
        // owning product's categories without also joining through variants.
        $valuesInScope = function ($query) use ($categoryIds) {
            if (! empty($categoryIds)) {
                $query->whereHas('productAttributeValues.product.categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        };

        $attributes = Attribute::with(['values' => $valuesInScope])
            ->whereHas('values', $valuesInScope)
            ->get()
            ->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'code' => $attr->code,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'values' => $attr->values->map(function ($val) {
                        return [
                            'id' => $val->id,
                            'value' => $val->value,
                        ];
                    }),
                ];
            });

        return [
            'price' => [
                'min' => $priceStats->min_price ? floor($priceStats->min_price) : 0,
                'max' => $priceStats->max_price ? ceil($priceStats->max_price) : 200000,
            ],
            'attributes' => $attributes,
        ];
    }
}
