<?php

namespace App\Api\Admin\Dto;

class CouponDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $type,
        public readonly float $amount,
        public readonly ?int $usageLimit,
        public readonly ?string $expiresAt,
        public readonly bool $isActive
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: strtoupper($request->input('code')),
            type: $request->input('type'),
            amount: (float) $request->input('amount'),
            usageLimit: $request->input('usageLimit') ? (int) $request->input('usageLimit') : null,
            expiresAt: $request->input('expiresAt'),
            isActive: (bool) $request->input('isActive', true)
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'amount' => $this->amount,
            'usage_limit' => $this->usageLimit,
            'expires_at' => $this->expiresAt,
            'is_active' => $this->isActive,
        ];
    }
}
