<?php

namespace App\Api\Admin\Actions\Order;

use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use App\Models\Order;

class GetAdminOrderDetailsAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(int $id): Order
    {
        $order = $this->orderRepository->find($id);

        if (! $order) {
            throw new OrderNotFoundException;
        }

        return $order->load('items');
    }
}
