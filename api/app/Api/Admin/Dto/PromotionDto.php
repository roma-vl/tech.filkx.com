<?php

namespace App\Api\Admin\Dto;

class PromotionDto
{
    /**
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $productIds
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $type,
        public readonly float $amount,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly bool $isActive,
        public readonly array $categoryIds = [],
        public readonly array $productIds = []
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->input('name'),
            description: $request->input('description'),
            type: $request->input('type'),
            amount: (float) $request->input('amount'),
            startDate: $request->input('startDate'),
            endDate: $request->input('endDate'),
            isActive: (bool) $request->input('isActive', true),
            categoryIds: $request->input('categoryIds', []),
            productIds: $request->input('productIds', [])
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'amount' => $this->amount,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_active' => $this->isActive,
        ];
    }
}
