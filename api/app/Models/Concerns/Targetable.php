<?php

namespace App\Models\Concerns;

/**
 * Shared "does this discount apply to this cart item" logic for Coupon and Promotion.
 * An untargeted discountable (no category/product rows attached) applies to everything —
 * this is what makes every pre-existing coupon/promotion row keep working unchanged.
 */
trait Targetable
{
    public function hasTargeting(): bool
    {
        return $this->categories->isNotEmpty() || $this->products->isNotEmpty();
    }

    public function appliesToItem(int $productId, array $categoryIds): bool
    {
        if (! $this->hasTargeting()) {
            return true;
        }

        if ($this->products->contains('id', $productId)) {
            return true;
        }

        return $this->categories->pluck('id')->intersect($categoryIds)->isNotEmpty();
    }
}
