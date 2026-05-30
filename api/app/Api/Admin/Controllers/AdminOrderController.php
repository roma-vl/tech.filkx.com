<?php

namespace App\Api\Admin\Controllers;

use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminOrderController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with('items')->orderBy('created_at', 'desc')->get();

        $mapped = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'createdAt' => $order->created_at->toIso8601String(),
                'customerName' => $order->customer_name,
                'customerEmail' => $order->customer_email,
                'customerPhone' => $order->customer_phone,
                'shippingCountry' => $order->shipping_country,
                'shippingCity' => $order->shipping_city,
                'shippingAddress' => $order->shipping_address,
                'deliveryMethod' => $order->delivery_method,
                'paymentMethod' => $order->payment_method,
                'paymentStatus' => $order->payment_status,
                'status' => $order->status,
                'total' => (float) $order->total_price,
                'carrier' => $order->carrier,
                'trackingNumber' => $order->tracking_number,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'sku' => $item->sku,
                        'price' => (float) $item->price,
                        'qty' => $item->quantity,
                    ];
                }),
            ];
        });

        return self::successfulResponseWithData($mapped);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);

        return self::successfulResponseWithData($order);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending_payment,paid,processing,packed,shipped,delivered,completed,cancelled,refunded',
            'carrier' => 'nullable|string',
            'tracking_number' => 'nullable|string',
        ]);

        $order = Order::with('items.variant.stocks')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        DB::beginTransaction();
        try {
            // Apply Carrier and Tracking fields
            if ($request->has('carrier')) {
                $order->carrier = $request->input('carrier');
            }
            if ($request->has('tracking_number')) {
                $order->tracking_number = $request->input('tracking_number');
            }

            if ($oldStatus !== $newStatus) {
                // Stock transition logic
                foreach ($order->items as $item) {
                    if (! $item->variant) {
                        continue;
                    }
                    $stock = Stock::where('variant_id', $item->variant_id)->first();
                    if (! $stock) {
                        continue;
                    }

                    // Case A: transitioning from pending_payment to paid/processing (conversion)
                    if ($oldStatus === 'pending_payment' && in_array($newStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed'])) {
                        $stock->decrement('reserved', $item->quantity);
                        $stock->decrement('quantity', $item->quantity);
                    }
                    // Case B: transitioning from pending_payment to cancelled/refunded (release reservation)
                    elseif ($oldStatus === 'pending_payment' && in_array($newStatus, ['cancelled', 'refunded'])) {
                        $stock->decrement('reserved', $item->quantity);
                    }
                    // Case C: transitioning from paid/processing/completed to cancelled/refunded (return stock)
                    elseif (in_array($oldStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed']) && in_array($newStatus, ['cancelled', 'refunded'])) {
                        $stock->increment('quantity', $item->quantity);
                    }
                    // Case D: transitioning from cancelled/refunded back to paid/processing (re-deduct)
                    elseif (in_array($oldStatus, ['cancelled', 'refunded']) && in_array($newStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed'])) {
                        $stock->decrement('quantity', $item->quantity);
                    }
                }

                $order->status = $newStatus;

                // Auto-sync payment status
                if (in_array($newStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed'])) {
                    $order->payment_status = 'paid';
                } elseif ($newStatus === 'refunded') {
                    $order->payment_status = 'refunded';
                } elseif ($newStatus === 'cancelled') {
                    $order->payment_status = 'failed';
                }
            }

            $order->save();
            DB::commit();

            return self::successfulResponseWithData($order->load('items'));
        } catch (\Exception $e) {
            DB::rollBack();

            return self::errorResponse('Failed to update status: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return self::successfulResponse();
    }
}
