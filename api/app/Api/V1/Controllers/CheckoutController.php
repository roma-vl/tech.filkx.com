<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Checkout\PlaceOrderAction;
use App\Api\V1\Dto\PlaceOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Exceptions\EmptyCartException;
use App\Api\V1\Requests\PlaceOrderRequest;
use App\Api\V1\Resources\CheckoutOrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends BaseApiController
{
    public function placeOrder(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        try {
            $order = $action->execute(PlaceOrderDto::fromRequest($request));

            return self::successfulResponseWithData(new CheckoutOrderResource($order), Response::HTTP_CREATED);
        } catch (EmptyCartException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (CheckoutValidationException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return self::errorResponse('Помилка при створенні замовлення: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function quickOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:50',
            'variantId' => 'required|integer|exists:product_variants,id',
        ]);

        try {
            $order = DB::transaction(function () use ($validated) {
                $variant = ProductVariant::with(['product', 'stocks'])
                    ->lockForUpdate()
                    ->find($validated['variantId']);

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
                    'customer_name' => $validated['customerName'],
                    'customer_email' => auth('api')->user()?->email ?? 'quick-order@electro.com',
                    'customer_phone' => $validated['customerPhone'],
                    'shipping_country' => 'Ukraine',
                    'shipping_city' => 'Київ',
                    'shipping_address' => 'Швидке замовлення (передзвонити для уточнення деталей)',
                    'delivery_method' => 'nova_poshta',
                    'payment_method' => 'cod',
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

                return $order;
            });

            return self::successfulResponseWithData(new CheckoutOrderResource($order->load('items')), Response::HTTP_CREATED);
        } catch (CheckoutValidationException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return self::errorResponse('Помилка при створенні швидкого замовлення: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
