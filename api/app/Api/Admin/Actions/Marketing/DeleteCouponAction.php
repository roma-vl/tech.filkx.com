<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\V1\Exceptions\CouponNotFoundException;
use App\Api\V1\Repositories\CouponRepositoryInterface;

class DeleteCouponAction
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function execute(int $id): void
    {
        $coupon = $this->couponRepository->find($id);

        if (! $coupon) {
            throw new CouponNotFoundException;
        }

        $this->couponRepository->delete($coupon);
    }
}
