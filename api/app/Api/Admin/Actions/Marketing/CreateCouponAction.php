<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\Admin\Dto\CouponDto;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Models\Coupon;

class CreateCouponAction
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function execute(CouponDto $dto): Coupon
    {
        return $this->couponRepository->create($dto->toArray());
    }
}
