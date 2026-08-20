<?php

namespace App\Services\Pricing\Dto;

class PriceCalculationResultDto
{
    /**
     * @param  array<int, int>  $appliedPromotionIds
     */
    public function __construct(
        public readonly float $subtotal,
        public readonly float $couponDiscount,
        public readonly float $promotionDiscount,
        public readonly float $totalDiscount,
        public readonly float $total,
        public readonly array $appliedPromotionIds = []
    ) {}
}
