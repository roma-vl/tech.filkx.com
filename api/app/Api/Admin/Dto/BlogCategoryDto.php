<?php

namespace App\Api\Admin\Dto;

class BlogCategoryDto
{
    public function __construct(
        public readonly string $nameUk,
        public readonly string $nameEn,
        public readonly ?string $descriptionUk,
        public readonly ?string $descriptionEn,
        public readonly int $order,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            nameUk: $request->input('nameUk'),
            nameEn: $request->input('nameEn'),
            descriptionUk: $request->input('descriptionUk'),
            descriptionEn: $request->input('descriptionEn'),
            order: (int) $request->input('order', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => ['uk' => $this->nameUk, 'en' => $this->nameEn],
            'description' => ['uk' => $this->descriptionUk ?? '', 'en' => $this->descriptionEn ?? ''],
            'order' => $this->order,
        ];
    }
}
