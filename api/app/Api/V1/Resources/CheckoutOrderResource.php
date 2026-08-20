<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CheckoutOrderResource',
    title: 'Checkout Order Resource',
)]
class CheckoutOrderResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'orderNumber', type: 'string', example: 'FKX-20260818-AB12CD')]
    #[OA\Property(property: 'userId', type: 'integer', nullable: true)]
    #[OA\Property(property: 'customerName', type: 'string')]
    #[OA\Property(property: 'customerEmail', type: 'string', format: 'email')]
    #[OA\Property(property: 'customerPhone', type: 'string')]
    #[OA\Property(property: 'shippingCountry', type: 'string', nullable: true)]
    #[OA\Property(property: 'shippingCity', type: 'string', nullable: true)]
    #[OA\Property(property: 'shippingAddress', type: 'string')]
    #[OA\Property(property: 'deliveryMethod', type: 'string')]
    #[OA\Property(property: 'paymentMethod', type: 'string')]
    #[OA\Property(property: 'paymentStatus', type: 'string', example: 'pending')]
    #[OA\Property(property: 'status', type: 'string', example: 'pending_payment')]
    #[OA\Property(property: 'totalPrice', type: 'number', format: 'float')]
    #[OA\Property(property: 'couponCode', type: 'string', nullable: true)]
    #[OA\Property(property: 'discountAmount', type: 'number', format: 'float')]
    #[OA\Property(property: 'createdAt', type: 'string', format: 'date-time')]
    #[OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')]
    #[OA\Property(
        property: 'items',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'orderId', type: 'integer'),
                new OA\Property(property: 'variantId', type: 'integer'),
                new OA\Property(property: 'productName', type: 'string'),
                new OA\Property(property: 'sku', type: 'string'),
                new OA\Property(property: 'price', type: 'number', format: 'float'),
                new OA\Property(property: 'quantity', type: 'integer'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
            ],
            type: 'object',
        ),
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orderNumber' => $this->order_number,
            'userId' => $this->user_id,
            'customerName' => $this->customer_name,
            'customerEmail' => $this->customer_email,
            'customerPhone' => $this->customer_phone,
            'shippingCountry' => $this->shipping_country,
            'shippingCity' => $this->shipping_city,
            'shippingAddress' => $this->shipping_address,
            'deliveryMethod' => $this->delivery_method,
            'paymentMethod' => $this->payment_method,
            'paymentStatus' => $this->payment_status,
            'status' => $this->status,
            'totalPrice' => (float) $this->total_price,
            'couponCode' => $this->coupon_code,
            'discountAmount' => (float) $this->discount_amount,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'orderId' => $item->order_id,
                    'variantId' => $item->variant_id,
                    'productName' => $item->product_name,
                    'sku' => $item->sku,
                    'price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'createdAt' => $item->created_at,
                    'updatedAt' => $item->updated_at,
                ];
            }),
        ];
    }
}
