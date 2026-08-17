<?php

namespace App\Models\Contracts;

/**
 * Implemented by both Coupon and Promotion so PriceCalculationService can treat
 * them uniformly when deciding what applies to a given cart line item.
 */
interface Discountable
{
    public function appliesToItem(int $productId, array $categoryIds): bool;
}
