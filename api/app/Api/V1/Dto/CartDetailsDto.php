<?php

namespace App\Api\V1\Dto;

class CartDetailsDto
{
    public function __construct(
        public readonly string $sessionId,
        public readonly array $items,
        public readonly float $total,
        public readonly float $promotionDiscount = 0.0,
        public readonly float $discountedTotal = 0.0
    ) {}
}
