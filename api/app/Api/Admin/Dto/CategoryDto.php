<?php

namespace App\Api\Admin\Dto;

class CategoryDto
{
    public function __construct(
        public readonly string $nameUk,
        public readonly string $nameEn,
        public readonly ?int $parentId,
        public readonly int $order,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            nameUk: $request->input('nameUk'),
            nameEn: $request->input('nameEn'),
            parentId: $request->input('parentId') ? (int) $request->input('parentId') : null,
            order: (int) $request->input('order', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => ['uk' => $this->nameUk, 'en' => $this->nameEn],
            'parent_id' => $this->parentId,
            'order' => $this->order,
        ];
    }
}
