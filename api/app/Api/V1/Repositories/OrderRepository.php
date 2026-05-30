<?php

namespace App\Api\V1\Repositories;

use App\Models\Order;
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
}
