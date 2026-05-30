<?php

namespace App\Api\V1\Dto;

class CartDetailsDto
{
    public function __construct(
        public readonly string $sessionId,
        public readonly array $items,
        public readonly float $total
    ) {}
}
