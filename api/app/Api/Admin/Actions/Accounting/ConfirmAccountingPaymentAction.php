<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use App\Models\Order;

class ConfirmAccountingPaymentAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(int $id, bool $approve): Order
    {
        $order = $this->orderRepository->find($id);

        if (! $order) {
            throw new OrderNotFoundException;
        }

        if ($approve) {
            $this->orderRepository->update($order, [
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);
        } else {
            $this->orderRepository->update($order, [
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
        }

        return $order;
    }
}
