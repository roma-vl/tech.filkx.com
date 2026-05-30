<?php

namespace App\Api\Admin\Dto;

class UpdateOrderStatusDto
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $carrier,
        public readonly ?string $trackingNumber
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            status: $request->input('status'),
            carrier: $request->input('carrier'),
            trackingNumber: $request->input('tracking_number')
        );
    }
}
