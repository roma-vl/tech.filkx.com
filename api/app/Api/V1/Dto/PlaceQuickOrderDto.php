<?php

namespace App\Api\V1\Dto;

class PlaceQuickOrderDto
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly int $variantId,
        public readonly string $paymentMethod
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            customerName: $request->input('customerName'),
            customerPhone: $request->input('customerPhone'),
            variantId: (int) $request->input('variantId'),
            paymentMethod: $request->input('paymentMethod', 'cod')
        );
    }
}
