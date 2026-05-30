<?php

namespace App\Api\Admin\Actions\Order;

use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Api\V1\Repositories\OrderRepositoryInterface;

class DeleteAdminOrderAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(int $id): void
    {
        $order = $this->orderRepository->find($id);

        if (! $order) {
            throw new OrderNotFoundException();
        }

        $this->orderRepository->delete($order);
    }
}
