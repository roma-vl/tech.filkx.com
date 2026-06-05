<?php

namespace App\Api\V1\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function allWithItems(): Collection;

    public function findWithItems(int $id): ?Order;

    public function find(int $id): ?Order;

    public function update(Order $order, array $data): Order;

    public function delete(Order $order): bool;

    public function paginateLedger(array $filters, int $perPage): LengthAwarePaginator;

    public function paginateInvoices(array $filters, int $perPage): LengthAwarePaginator;

    public function paginatePendingPayments(int $perPage): LengthAwarePaginator;

    public function getAccountingStats(): array;

    public function getBillingStats(): array;

    public function getCompletedAndCancelledOrders(): Collection;
}
