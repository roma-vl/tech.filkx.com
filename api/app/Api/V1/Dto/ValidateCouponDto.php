<?php

namespace App\Api\V1\Dto;

class ValidateCouponDto
{
    public function __construct(
        public readonly string $code,
        public readonly CartSessionDto $cartSession
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: $request->input('code'),
            cartSession: CartSessionDto::fromRequest($request)
        );
    }
}
