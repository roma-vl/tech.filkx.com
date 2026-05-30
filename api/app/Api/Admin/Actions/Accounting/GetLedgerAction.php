<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Api\Admin\Dto\LedgerFilterDto;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetLedgerAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(LedgerFilterDto $dto, int $perPage): LengthAwarePaginator
    {
        return $this->orderRepository->paginateLedger($dto->toArray(), $perPage);
    }
}
