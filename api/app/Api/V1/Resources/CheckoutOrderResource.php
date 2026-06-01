<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'orderNumber'    => $this->order_number,
            'userId'         => $this->user_id,
            'customerName'   => $this->customer_name,
            'customerEmail'  => $this->customer_email,
            'customerPhone'  => $this->customer_phone,
            'shippingCountry' => $this->shipping_country,
            'shippingCity'   => $this->shipping_city,
            'shippingAddress' => $this->shipping_address,
            'deliveryMethod' => $this->delivery_method,
            'paymentMethod'  => $this->payment_method,
            'paymentStatus'  => $this->payment_status,
            'status'         => $this->status,
            'totalPrice'     => (float) $this->total_price,
            'couponCode'     => $this->coupon_code,
            'discountAmount' => (float) $this->discount_amount,
            'createdAt'      => $this->created_at,
            'updatedAt'      => $this->updated_at,
            'items'          => $this->items->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'orderId'     => $item->order_id,
                    'variantId'   => $item->variant_id,
                    'productName' => $item->product_name,
                    'sku'         => $item->sku,
                    'price'       => (float) $item->price,
                    'quantity'    => (int) $item->quantity,
                    'createdAt'   => $item->created_at,
                    'updatedAt'   => $item->updated_at,
                ];
            }),
        ];
    }
}
