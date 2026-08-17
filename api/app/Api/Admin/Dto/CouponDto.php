<?php

namespace App\Api\Admin\Dto;

class CouponDto
{
    /**
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $productIds
     */
    public function __construct(
        public readonly string $code,
        public readonly string $type,
        public readonly float $amount,
        public readonly ?int $usageLimit,
        public readonly ?string $expiresAt,
        public readonly bool $isActive,
        public readonly array $categoryIds = [],
        public readonly array $productIds = []
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: strtoupper($request->input('code')),
            type: $request->input('type'),
            amount: (float) $request->input('amount'),
            usageLimit: $request->input('usageLimit') ? (int) $request->input('usageLimit') : null,
            expiresAt: $request->input('expiresAt'),
            isActive: (bool) $request->input('isActive', true),
            categoryIds: $request->input('categoryIds', []),
            productIds: $request->input('productIds', [])
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
