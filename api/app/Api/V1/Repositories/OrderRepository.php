<?php

namespace App\Api\V1\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function allWithItems(): Collection
    {
        return Order::with('items')->orderBy('created_at', 'desc')->get();
    }

    public function findWithItems(int $id): ?Order
    {
        return Order::with('items.variant.stocks')->find($id);
    }

    public function find(int $id): ?Order
    {
        return Order::find($id);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function delete(Order $order): bool
    {
        return (bool) $order->delete();
    }

    public function paginateLedger(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Order::with('user');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['type'])) {
            $type = $filters['type'];
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

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateInvoices(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Order::with('user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'paid') {
                $query->where('status', 'completed');
            } elseif ($status === 'issued') {
                $query->where('status', 'pending');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginatePendingPayments(int $perPage): LengthAwarePaginator
    {
        return Order::with('user')->where('status', 'pending')->paginate($perPage);
    }

    public function getAccountingStats(): array
    {
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $refunds = Order::where('status', 'cancelled')->sum('total_price');

        return [
            'totalRevenueMinor' => (int) ($revenue * 100),
            'totalRefundsMinor' => (int) ($refunds * 100),
            'netRevenueMinor' => (int) (($revenue - $refunds) * 100),
        ];
    }

    public function getBillingStats(): array
    {
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $pendingPayments = Order::where('status', 'pending')->count();

        return [
            'revenueMinor' => (int) ($revenue * 100),
            'activeSubscriptions' => 0,
            'pendingPaymentsCount' => $pendingPayments,
        ];
    }

    public function getCompletedAndCancelledOrders(): Collection
    {
        return Order::with('user')->whereIn('status', ['completed', 'cancelled'])->get();
    }
}
