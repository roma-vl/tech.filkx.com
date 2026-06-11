<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\Admin\Dto\MarketingFilterDto;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCouponsAction
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function execute(MarketingFilterDto $dto, int $perPage): LengthAwarePaginator
    {
        return $this->couponRepository->paginate($dto->toArray(), $perPage);
    }
}
