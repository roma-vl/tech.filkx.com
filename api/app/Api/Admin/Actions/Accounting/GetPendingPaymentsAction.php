<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetPendingPaymentsAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(int $perPage): LengthAwarePaginator
    {
        return $this->orderRepository->paginatePendingPayments($perPage);
    }
}
