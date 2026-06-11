<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\Admin\Dto\CouponDto;
use App\Api\V1\Exceptions\CouponNotFoundException;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Models\Coupon;

class UpdateCouponAction
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function execute(int $id, CouponDto $dto): Coupon
    {
        $coupon = $this->couponRepository->find($id);

        if (! $coupon) {
            throw new CouponNotFoundException;
        }

        return $this->couponRepository->update($coupon, $dto->toArray());
    }
}
