<?php

namespace App\Api\V1\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends BaseApiController
{
    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'required|email|max:255',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_address' => 'required|string|max:500',
            'delivery_method' => 'required|string|max:100',
            'payment_method' => 'required|string|max:100',
            'session_id' => 'nullable|string',
        ]);

        $user = auth('api')->user();
        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } else {
            $sessionId = $request->header('X-Cart-Session-ID') ?: $request->input('session_id');
            $cart = Cart::where('session_id', $sessionId)->first();
        }

        if (! $cart || $cart->items()->count() === 0) {
            return self::errorResponse('Корзина порожня', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            $orderItemsData = [];
            $totalPrice = 0;

            foreach ($cart->items as $item) {
                $variant = ProductVariant::with(['product', 'stocks'])->lockForUpdate()->find($item->variant_id);

                if (! $variant || $variant->product->status !== 'active') {
                    DB::rollBack();

                    return self::errorResponse("Товар {$item->variant->sku} недоступний", Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // Check stock availability
                $stock = $variant->stocks->first(); // assume single main warehouse for simplicity
                if (! $stock) {
                    DB::rollBack();

                    return self::errorResponse("Складські дані для {$variant->sku} відсутні", Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $availableStock = $stock->quantity - $stock->reserved;
                if ($item->quantity > $availableStock) {
                    DB::rollBack();

                    return self::errorResponse("Недостатньо товару {$variant->sku} в наявності (доступно: $availableStock)", Response::HTTP_UNPROCESSABLE_ENTITY);
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

            // Generate unique order number: FKX-YYYYMMDD-XXXXXX
            $orderNumber = 'FKX-'.date('Ymd').'-'.strtoupper(str_random(6));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'FKX-'.date('Ymd').'-'.strtoupper(str_random(6));
            }

            // Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user ? $user->id : null,
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'customer_phone' => $request->input('customer_phone'),
                'shipping_country' => $request->input('shipping_country'),
                'shipping_city' => $request->input('shipping_city'),
                'shipping_address' => $request->input('shipping_address'),
                'delivery_method' => $request->input('delivery_method'),
                'payment_method' => $request->input('payment_method'),
                'payment_status' => 'pending',
                'status' => 'pending_payment',
                'total_price' => $totalPrice,
            ]);

            // Save order items snapshot
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // Clear Cart
            $cart->items()->delete();

            DB::commit();

            return self::successfulResponseWithData($order->load('items'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return self::errorResponse('Помилка при створенні замовлення: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
