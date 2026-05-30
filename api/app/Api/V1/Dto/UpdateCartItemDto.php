<?php

namespace App\Api\V1\Dto;

class UpdateCartItemDto
{
    public function __construct(
        public readonly int $quantity
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            quantity: (int) $request->input('quantity')
        );
    }
}
