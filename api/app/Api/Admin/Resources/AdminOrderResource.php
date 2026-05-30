<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orderNumber' => $this->order_number,
            'createdAt' => $this->created_at->toIso8601String(),
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
            'total' => (float) $this->total_price,
            'carrier' => $this->carrier,
            'trackingNumber' => $this->tracking_number,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'sku' => $item->sku,
                    'price' => (float) $item->price,
                    'qty' => $item->quantity,
                ];
            }),
        ];
    }
}
