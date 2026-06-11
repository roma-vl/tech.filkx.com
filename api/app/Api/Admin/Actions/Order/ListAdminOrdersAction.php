<?php

namespace App\Api\Admin\Actions\Order;

use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListAdminOrdersAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(): Collection
    {
        return $this->orderRepository->allWithItems();
    }
}
