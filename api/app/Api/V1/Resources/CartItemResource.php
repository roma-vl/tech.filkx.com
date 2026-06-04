<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this['id'],
            'variantId' => $this['variant_id'],
            'productId' => $this['product_id'],
            'name'      => $this['name'],
            'slug'      => $this['slug'],
            'sku'       => $this['sku'],
            'price'     => (float) $this['price'],
            'oldPrice'  => $this['oldPrice'] ? (float) $this['oldPrice'] : null,
            'quantity'  => (int) $this['quantity'],
            'stock'     => (int) $this['stock'],
            'image'     => $this['image'],
            'subtotal'  => (float) $this['subtotal'],
        ];
    }
}
