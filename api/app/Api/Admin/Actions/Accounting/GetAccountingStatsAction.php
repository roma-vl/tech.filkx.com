<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Api\V1\Repositories\OrderRepositoryInterface;

class GetAccountingStatsAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(): array
    {
        return $this->orderRepository->getAccountingStats();
    }
}
