<?php

namespace App\Services\Pricing;

use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Models\Contracts\Discountable;
use App\Services\Pricing\Dto\CartLineItemDto;
use App\Services\Pricing\Dto\PriceCalculationResultDto;

/**
 * Single source of truth for "what discount applies to this cart". Coupon eligibility
 * (active/expired/usage limit) is decided by the caller (e.g. ValidateCouponAction) —
 * this service assumes any Coupon passed in has already been validated and only
 * answers "how much, on which items".
 *
 * Stacking policy: a Coupon (user-entered code, at most one per order) and any number
 * of site-wide Promotions can combine — but never on the same line item. When a
 * Promotion and the Coupon both target an item, whichever gives the larger discount
 * wins for that item and the other is not applied there, so a customer is never
 * discounted twice into a loss-making price on a single unit.
 */
class PriceCalculationService
{
    public function __construct(
        private readonly PromotionRepositoryInterface $promotionRepository
    ) {}

    /**
     * @param  CartLineItemDto[]  $lineItems
     */
    public function calculate(array $lineItems, ?Discountable $coupon = null): PriceCalculationResultDto
    {
        $subtotal = array_sum(array_map(static fn (CartLineItemDto $item) => $item->subtotal(), $lineItems));

        if ($subtotal <= 0.0 || empty($lineItems)) {
            return new PriceCalculationResultDto($subtotal, 0.0, 0.0, 0.0, $subtotal);
        }

        $couponItemDiscounts = $coupon ? $this->itemDiscounts($lineItems, $coupon) : [];

        $promotions = $this->promotionRepository->activePromotions();
        $promotionItemDiscounts = $promotions->mapWithKeys(
            fn ($promotion) => [$promotion->id => [$promotion, $this->itemDiscounts($lineItems, $promotion)]]
        );

        $couponDiscountTotal = 0.0;
        $promotionDiscountTotal = 0.0;
        $appliedPromotionIds = [];

        foreach (array_keys($lineItems) as $index) {
            $couponAmount = $couponItemDiscounts[$index] ?? 0.0;

            $bestPromotion = null;
            $bestPromotionAmount = 0.0;
            foreach ($promotionItemDiscounts as [$promotion, $discounts]) {
                $amount = $discounts[$index] ?? 0.0;
                if ($amount > $bestPromotionAmount) {
                    $bestPromotionAmount = $amount;
                    $bestPromotion = $promotion;
                }
            }

            if ($couponAmount <= 0.0 && $bestPromotionAmount <= 0.0) {
                continue;
            }

            if ($couponAmount >= $bestPromotionAmount) {
                $couponDiscountTotal += $couponAmount;
            } else {
                $promotionDiscountTotal += $bestPromotionAmount;
                $appliedPromotionIds[$bestPromotion->id] = $bestPromotion->id;
            }
        }

        $totalDiscount = min($couponDiscountTotal + $promotionDiscountTotal, $subtotal);

        return new PriceCalculationResultDto(
            subtotal: round($subtotal, 2),
            couponDiscount: round($couponDiscountTotal, 2),
            promotionDiscount: round($promotionDiscountTotal, 2),
            totalDiscount: round($totalDiscount, 2),
            total: round($subtotal - $totalDiscount, 2),
            appliedPromotionIds: array_values($appliedPromotionIds)
        );
    }

    /**
     * @param  CartLineItemDto[]  $lineItems
     * @return array<int, float> discount amount keyed by line-item index
     */
    private function itemDiscounts(array $lineItems, Discountable $discountable): array
    {
        $matchedIndexes = [];
        $matchedSubtotal = 0.0;

        foreach ($lineItems as $index => $item) {
            if ($discountable->appliesToItem($item->productId, $item->categoryIds)) {
                $matchedIndexes[] = $index;
                $matchedSubtotal += $item->subtotal();
            }
        }

        if ($matchedSubtotal <= 0.0) {
            return [];
        }

        if ($discountable->type === 'percent') {
            $rate = min(max((float) $discountable->amount, 0.0), 100.0) / 100;

            return collect($matchedIndexes)
                ->mapWithKeys(fn ($index) => [$index => $lineItems[$index]->subtotal() * $rate])
                ->all();
        }

        // Fixed amount: a single lump sum, capped to and distributed proportionally
        // across the matched items so it never discounts more than what it targets.
        $cappedAmount = min(max((float) $discountable->amount, 0.0), $matchedSubtotal);

        return collect($matchedIndexes)
            ->mapWithKeys(function ($index) use ($lineItems, $cappedAmount, $matchedSubtotal) {
                $itemSubtotal = $lineItems[$index]->subtotal();

                return [$index => $cappedAmount * ($itemSubtotal / $matchedSubtotal)];
            })
            ->all();
    }
}
