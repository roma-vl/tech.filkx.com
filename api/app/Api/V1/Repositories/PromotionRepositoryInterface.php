<?php

namespace App\Api\V1\Repositories;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PromotionRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function find(int $id): ?Promotion;

    public function create(array $data): Promotion;

    public function update(Promotion $promotion, array $data): Promotion;

    public function delete(Promotion $promotion): bool;
}
