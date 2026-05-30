<?php

namespace App\Api\V1\Dto;

class ValidateCouponDto
{
    public function __construct(
        public readonly string $code,
        public readonly float $cartTotal
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: $request->input('code'),
            cartTotal: (float) $request->input('cart_total')
        );
    }
}
