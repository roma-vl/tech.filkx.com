<?php

namespace App\Services\Pricing\Dto;

class CartLineItemDto
{
    /**
     * @param  array<int, int>  $categoryIds
     */
    public function __construct(
        public readonly int $productId,
        public readonly array $categoryIds,
        public readonly float $unitPrice,
        public readonly int $quantity
    ) {}

    public function subtotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}
