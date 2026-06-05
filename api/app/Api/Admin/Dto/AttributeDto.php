<?php

namespace App\Api\Admin\Dto;

class AttributeDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $nameUk,
        public readonly string $nameEn,
        public readonly string $type,
        public readonly array $values,
        public readonly array $categoryIds = []
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: $request->input('code'),
            nameUk: $request->input('nameUk'),
            nameEn: $request->input('nameEn'),
            type: $request->input('type'),
            values: $request->input('values', []),
            categoryIds: $request->input('categoryIds', [])
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => [
                'uk' => $this->nameUk,
                'en' => $this->nameEn,
            ],
            'type' => $this->type,
        ];
    }
}
