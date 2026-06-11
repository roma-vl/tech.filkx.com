<?php

namespace App\Api\V1\Actions;

use App\Models\Attribute;
use Illuminate\Support\Facades\DB;

class GetCatalogFiltersAction
{
    public function execute(): array
    {
        // 1. Min/Max price of all active products
        $priceStats = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('products.status', 'active')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        // 2. Fetch all attributes with their values that have product assignments
        $attributes = Attribute::with(['values'])
            ->whereHas('values')
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
