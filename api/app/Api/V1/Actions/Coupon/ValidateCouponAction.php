<?php

namespace App\Api\V1\Actions\Coupon;

use App\Api\V1\Dto\ValidateCouponDto;
use App\Api\V1\Dto\ValidateCouponResultDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Repositories\CouponRepositoryInterface;

class ValidateCouponAction
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function execute(ValidateCouponDto $dto): ValidateCouponResultDto
    {
        $coupon = $this->couponRepository->findByCode($dto->code);

        if (! $coupon || ! $coupon->is_active) {
            throw new CheckoutValidationException('Купон недійсний або неактивний');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new CheckoutValidationException('Термін дії купона закінчився');
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new CheckoutValidationException('Купон вичерпав ліміт використання');
        }

        $discount = 0;
        if ($coupon->type === 'percent') {
            $discount = $dto->cartTotal * ($coupon->amount / 100);
        } else {
            $discount = $coupon->amount;
        }

        if ($discount > $dto->cartTotal) {
            $discount = $dto->cartTotal;
        }

        return new ValidateCouponResultDto(
            code: $coupon->code,
            type: $coupon->type,
            amount: (float) $coupon->amount,
            discount: (float) $discount
        );
    }
}
