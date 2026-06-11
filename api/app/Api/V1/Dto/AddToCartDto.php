<?php

namespace App\Api\V1\Dto;

class AddToCartDto
{
    public function __construct(
        public readonly mixed $variantId,
        public readonly int $quantity
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            variantId: $request->input('variantId'),
            quantity: (int) $request->input('quantity', 1)
        );
    }
}
