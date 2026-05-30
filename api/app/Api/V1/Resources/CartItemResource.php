<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'variant_id' => $this['variant_id'],
            'product_id' => $this['product_id'],
            'name' => $this['name'],
            'sku' => $this['sku'],
            'price' => (float) $this['price'],
            'oldPrice' => $this['oldPrice'] ? (float) $this['oldPrice'] : null,
            'quantity' => (int) $this['quantity'],
            'stock' => (int) $this['stock'],
            'image' => $this['image'],
            'subtotal' => (float) $this['subtotal'],
        ];
    }
}
