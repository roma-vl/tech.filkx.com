<?php

namespace App\Api\V1\Actions\Coupon;

use App\Api\V1\Actions\Cart\GetCartAction;
use App\Api\V1\Dto\ValidateCouponDto;
use App\Api\V1\Dto\ValidateCouponResultDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Services\Pricing\Dto\CartLineItemDto;
use App\Services\Pricing\PriceCalculationService;

class ValidateCouponAction
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository,
        protected GetCartAction $getCartAction,
        protected PriceCalculationService $priceCalculationService
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

        // The discount is calculated off the real, server-side cart (never a
        // client-supplied total) so targeting and stacking can't be spoofed.
        $cartDetails = $this->getCartAction->execute($dto->cartSession);

        if (empty($cartDetails->items)) {
            throw new CheckoutValidationException('Корзина порожня');
        }

        $lineItems = array_map(
            fn (array $item) => new CartLineItemDto(
                productId: $item['product_id'],
                categoryIds: $item['category_ids'],
                unitPrice: $item['price'],
                quantity: $item['quantity']
            ),
            $cartDetails->items
        );

        $result = $this->priceCalculationService->calculate($lineItems, $coupon);

        return new ValidateCouponResultDto(
            code: $coupon->code,
            type: $coupon->type,
            amount: (float) $coupon->amount,
            discount: $result->couponDiscount
        );
    }
}
