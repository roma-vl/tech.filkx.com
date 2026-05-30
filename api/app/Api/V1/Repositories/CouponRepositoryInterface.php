<?php

namespace App\Api\V1\Repositories;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CouponRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function find(int $id): ?Coupon;

    public function create(array $data): Coupon;

    public function update(Coupon $coupon, array $data): Coupon;

    public function delete(Coupon $coupon): bool;
}
