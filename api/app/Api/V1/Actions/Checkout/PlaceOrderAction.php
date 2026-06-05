<?php

namespace App\Api\V1\Actions\Checkout;

use App\Api\V1\Dto\PlaceOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Exceptions\EmptyCartException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlaceOrderAction
{
    public function execute(PlaceOrderDto $dto): Order
    {
        $user = auth('api')->user();
        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } else {
            $cart = Cart::where('session_id', $dto->sessionId)->first();
        }

        if (! $cart || $cart->items()->count() === 0) {
            throw new EmptyCartException;
        }

        return DB::transaction(function () use ($cart, $dto, $user) {
            $orderItemsData = [];
            $totalPrice = 0;

            foreach ($cart->items as $item) {
                $variant = ProductVariant::with(['product', 'stocks'])
                    ->lockForUpdate()
                    ->find($item->variant_id);

                if (! $variant || $variant->product->status !== 'active') {
                    throw new CheckoutValidationException("Товар {$item->variant->sku} недоступний");
                }

                // Check stock availability
                $stock = $variant->stocks->first(); // assume single main warehouse for simplicity
                if (! $stock) {
                    throw new CheckoutValidationException("Складські дані для {$variant->sku} відсутні");
                }

                $availableStock = $stock->quantity - $stock->reserved;
                if ($item->quantity > $availableStock) {
                    throw new CheckoutValidationException("Недостатньо товару {$variant->sku} в наявності (доступно: $availableStock)");
                }

                // Reserve quantity
                $stock->increment('reserved', $item->quantity);

                // Snapshot details
                $productName = $variant->product->name['uk'] ?? $variant->product->name['en'] ?? 'Товар';
                $price = (float) $variant->price;
                $quantity = $item->quantity;
                $totalPrice += $price * $quantity;

                $orderItemsData[] = [
                    'variant_id' => $variant->id,
                    'product_name' => $productName,
                    'sku' => $variant->sku,
                    'price' => $price,
                    'quantity' => $quantity,
                ];
            }

            // Coupon validation
            $discountAmount = 0.00;
            $couponCode = $dto->couponCode;
            $coupon = null;

            if ($couponCode) {
                $coupon = Coupon::where('code', strtoupper($couponCode))
                    ->where('is_active', true)
                    ->first();

                if (! $coupon) {
                    throw new CheckoutValidationException('Купон недійсний або неактивний');
                }

                // Check expiry
                if ($coupon->expires_at && $coupon->expires_at->isPast()) {
                    throw new CheckoutValidationException('Термін дії купона закінчився');
                }

                // Check usage limit
                if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                    throw new CheckoutValidationException('Купон вичерпав ліміт використання');
                }

                // Calculate discount
                if ($coupon->type === 'percent') {
                    $discountAmount = $totalPrice * ($coupon->amount / 100);
                } else {
                    $discountAmount = $coupon->amount;
                }

                if ($discountAmount > $totalPrice) {
                    $discountAmount = $totalPrice;
                }
            }

            // Generate unique order number: FKX-YYYYMMDD-XXXXXX
            $orderNumber = 'FKX-'.date('Ymd').'-'.strtoupper(Str::random(6));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'FKX-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }

            // Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user ? $user->id : null,
                'customer_name' => $dto->customerName,
                'customer_email' => $dto->customerEmail,
                'customer_phone' => $dto->customerPhone,
                'shipping_country' => $dto->shippingCountry,
                'shipping_city' => $dto->shippingCity,
                'shipping_address' => $dto->shippingAddress,
                'delivery_method' => $dto->deliveryMethod,
                'payment_method' => $dto->paymentMethod,
                'payment_status' => 'pending',
                'status' => 'pending_payment',
                'total_price' => $totalPrice - $discountAmount,
                'coupon_code' => $coupon ? $coupon->code : null,
                'discount_amount' => $discountAmount,
            ]);

            // Save order items snapshot
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Clear Cart
            $cart->items()->delete();

            return $order->load('items');
        });
    }
}
