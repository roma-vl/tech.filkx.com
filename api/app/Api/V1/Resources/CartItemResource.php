<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItemResource',
    title: 'Cart Item Resource',
)]
class CartItemResource extends JsonResource
{
    #[OA\Property(
        property: 'id',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'variantId',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'productId',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'name',
        type: 'string',
    )]
    #[OA\Property(
        property: 'slug',
        type: 'string',
    )]
    #[OA\Property(
        property: 'sku',
        type: 'string',
    )]
    #[OA\Property(
        property: 'price',
        type: 'number',
    )]
    #[OA\Property(
        property: 'oldPrice',
        type: 'number',
        nullable: true,
    )]
    #[OA\Property(
        property: 'quantity',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'stock',
        type: 'integer',
        description: 'Available stock (quantity minus reserved), clamped to the item quantity',
    )]
    #[OA\Property(
        property: 'image',
        type: 'string',
        nullable: true,
    )]
    #[OA\Property(
        property: 'subtotal',
        type: 'number',
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'variantId' => $this['variant_id'],
            'productId' => $this['product_id'],
            'name' => $this['name'],
            'slug' => $this['slug'],
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
