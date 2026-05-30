<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ExportLedgerCsvAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(): Collection
    {
        return $this->orderRepository->getCompletedAndCancelledOrders();
    }
}
