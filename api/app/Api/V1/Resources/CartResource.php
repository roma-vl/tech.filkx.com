<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartResource',
    title: 'Cart Resource',
)]
class CartResource extends JsonResource
{
    #[OA\Property(
        property: 'sessionId',
        type: 'string',
        nullable: true,
        description: 'Guest cart session identifier; null for an authenticated user\'s cart',
    )]
    #[OA\Property(
        property: 'items',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/CartItemResource'),
    )]
    #[OA\Property(
        property: 'total',
        type: 'number',
    )]
    #[OA\Property(
        property: 'promotionDiscount',
        type: 'number',
    )]
    #[OA\Property(
        property: 'discountedTotal',
        type: 'number',
    )]
    public function toArray(Request $request): array
    {
        return [
            'sessionId' => $this->sessionId,
            'items' => CartItemResource::collection($this->items),
            'total' => (float) $this->total,
            'promotionDiscount' => (float) $this->promotionDiscount,
            'discountedTotal' => (float) $this->discountedTotal,
        ];
    }
}
