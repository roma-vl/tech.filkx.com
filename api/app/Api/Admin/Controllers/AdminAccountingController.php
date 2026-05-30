<?php

namespace App\Api\Admin\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAccountingController extends BaseApiController
{
    public function ledger(Request $request): JsonResponse
    {
        $query = Order::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'charge') {
                $query->where('status', 'completed');
            } elseif ($type === 'refund') {
                $query->where('status', 'cancelled');
            } else {
                $query->whereNull('id');
            }
        } else {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(self::PER_PAGE);

        $items = collect($orders->items())->map(function ($order) {
            $isCompleted = $order->status === 'completed';
            return [
                'id' => $order->id,
                'user' => $order->user ? [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ] : [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                ],
                'type' => $isCompleted ? 'charge' : 'refund',
                'amountMinor' => $isCompleted ? (int)($order->total_price * 100) : -(int)($order->total_price * 100),
                'currency' => 'UAH',
                'referenceType' => 'order',
                'createdAt' => $order->created_at->toIso8601String(),
            ];
        });

        return self::successfulResponseWithData([
            'data' => $items,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $query = Order::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'paid') {
                $query->where('status', 'completed');
            } elseif ($status === 'issued') {
                $query->where('status', 'pending');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(self::PER_PAGE);

        $items = collect($orders->items())->map(function ($order) {
            $status = 'draft';
            if ($order->status === 'completed') {
                $status = 'paid';
            } elseif ($order->status === 'pending') {
                $status = 'issued';
            } elseif ($order->status === 'cancelled') {
                $status = 'cancelled';
            }

            return [
                'invoiceNumber' => $order->order_number,
                'user' => $order->user ? [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ] : [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                ],
                'totalMinor' => (int)($order->total_price * 100),
                'status' => $status,
                'currency' => 'UAH',
                'issuedAt' => $order->created_at->toIso8601String(),
                'createdAt' => $order->created_at->toIso8601String(),
            ];
        });

        return self::successfulResponseWithData([
            'data' => $items,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function accountingStats(): JsonResponse
    {
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $refunds = Order::where('status', 'cancelled')->sum('total_price');

        return self::successfulResponseWithData([
            'totalRevenueMinor' => (int)($revenue * 100),
            'totalRefundsMinor' => (int)($refunds * 100),
            'netRevenueMinor' => (int)(($revenue - $refunds) * 100),
        ]);
    }

    public function export(Request $request)
    {
        $orders = Order::with('user')->whereIn('status', ['completed', 'cancelled'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ledger_export.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Type', 'Amount', 'Currency', 'Reference', 'Date']);

            foreach ($orders as $order) {
                $isCompleted = $order->status === 'completed';
                fputcsv($file, [
                    $order->id,
                    $order->user ? $order->user->name : $order->customer_name,
                    $isCompleted ? 'charge' : 'refund',
                    $isCompleted ? $order->total_price : -$order->total_price,
                    'UAH',
                    'order',
                    $order->created_at->toIso8601String()
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function billingStats(): JsonResponse
    {
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $pendingPayments = Order::where('status', 'pending')->count();

        return self::successfulResponseWithData([
            'revenueMinor' => (int)($revenue * 100),
            'activeSubscriptions' => 0,
            'pendingPaymentsCount' => $pendingPayments,
        ]);
    }

    public function pendingPayments(): JsonResponse
    {
        $pendingOrders = Order::with('user')->where('status', 'pending')->paginate(self::PER_PAGE);

        $items = collect($pendingOrders->items())->map(function ($order) {
            return [
                'id' => $order->id,
                'user' => $order->user ? [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ] : [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                ],
                'amountMinor' => (int)($order->total_price * 100),
                'currency' => 'UAH',
                'createdAt' => $order->created_at->toIso8601String(),
            ];
        });

        return self::successfulResponseWithData([
            'data' => $items,
            'meta' => [
                'current_page' => $pendingOrders->currentPage(),
                'last_page' => $pendingOrders->lastPage(),
                'per_page' => self::PER_PAGE,
                'total' => $pendingOrders->total(),
            ]
        ]);
    }

    public function confirmPayment(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $approve = $request->input('approve');

        if ($approve) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);
            return self::successfulResponse('Payment approved and order completed.');
        } else {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
            return self::successfulResponse('Payment rejected and order cancelled.');
        }
    }

    public function viewProof($id): JsonResponse
    {
        return self::successfulResponseWithData([
            'id' => $id,
            'proofUrl' => null,
            'note' => 'No payment proof uploaded for this order.'
        ]);
    }

    public function subscriptions(): JsonResponse
    {
        return self::successfulResponseWithData([
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => self::PER_PAGE,
                'total' => 0,
            ]
        ]);
    }
}
