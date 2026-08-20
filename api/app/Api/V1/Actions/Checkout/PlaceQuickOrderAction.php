<?php

namespace App\Api\V1\Actions\Checkout;

use App\Api\V1\Dto\PlaceQuickOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaceQuickOrderAction
{
    public function execute(PlaceQuickOrderDto $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $variant = ProductVariant::with(['product', 'stocks'])
                ->lockForUpdate()
                ->find($dto->variantId);

            if (! $variant || $variant->product->status !== 'active') {
                throw new CheckoutValidationException('Товар недоступний');
            }

            // Check stock availability
            $stock = $variant->stocks->first();
            if ($stock) {
                $availableStock = $stock->quantity - $stock->reserved;
                if ($availableStock < 1) {
                    throw new CheckoutValidationException('Недостатньо товару в наявності');
                }
                // Reserve quantity
                $stock->increment('reserved', 1);
            }

            // Snapshot details
            $productName = $variant->product->name['uk'] ?? $variant->product->name['en'] ?? 'Товар';
            $price = (float) $variant->price;

            // Generate unique order number: FKX-YYYYMMDD-XXXXXX
            $orderNumber = 'FKX-'.date('Ymd').'-'.strtoupper(Str::random(6));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'FKX-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }

            // Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth('api')->id(),
                'customer_name' => $dto->customerName,
                'customer_email' => auth('api')->user()?->email ?? 'quick-order@electro.com',
                'customer_phone' => $dto->customerPhone,
                'shipping_country' => 'Ukraine',
                'shipping_city' => 'Київ',
                'shipping_address' => 'Швидке замовлення (передзвонити для уточнення деталей)',
                'delivery_method' => 'nova_poshta',
                'payment_method' => $dto->paymentMethod,
                'payment_status' => 'pending',
                'status' => 'pending_payment',
                'total_price' => $price,
                'discount_amount' => 0,
            ]);

            // Save order item snapshot
            OrderItem::create([
                'order_id' => $order->id,
                'variant_id' => $variant->id,
                'product_name' => $productName,
                'sku' => $variant->sku,
                'price' => $price,
                'quantity' => 1,
            ]);

            return $order->load('items');
        });
    }
}
