<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Api\Admin\Dto\InvoiceFilterDto;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetInvoicesAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(InvoiceFilterDto $dto, int $perPage): LengthAwarePaginator
    {
        return $this->orderRepository->paginateInvoices($dto->toArray(), $perPage);
    }
}
