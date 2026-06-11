<?php

namespace App\Api\V1\Dto;

class ValidateCouponResultDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $type,
        public readonly float $amount,
        public readonly float $discount
    ) {}
}
