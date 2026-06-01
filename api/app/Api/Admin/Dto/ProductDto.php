<?php

namespace App\Api\Admin\Dto;

class ProductDto
{
    public function __construct(
        public readonly string $nameUk,
        public readonly string $nameEn,
        public readonly ?string $descriptionUk,
        public readonly ?string $descriptionEn,
        public readonly string $status,
        public readonly bool $isHot,
        public readonly bool $isRecommended,
        public readonly ?int $brandId,
        public readonly int $categoryId,
        public readonly array $variants
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            nameUk: $request->input('nameUk'),
            nameEn: $request->input('nameEn'),
            descriptionUk: $request->input('descriptionUk'),
            descriptionEn: $request->input('descriptionEn'),
            status: $request->input('status'),
            isHot: (bool) $request->input('isHot', false),
            isRecommended: (bool) $request->input('isRecommended', false),
            brandId: $request->input('brandId') ? (int) $request->input('brandId') : null,
            categoryId: (int) $request->input('categoryId'),
            variants: $request->input('variants', [])
        );
    }

    public function toArray(): array
    {
        return [
            'brand_id' => $this->brandId,
            'name' => [
                'uk' => $this->nameUk,
                'en' => $this->nameEn,
            ],
            'description' => [
                'uk' => $this->descriptionUk ?? '',
                'en' => $this->descriptionEn ?? '',
            ],
            'status' => $this->status,
            'is_hot' => $this->isHot,
            'is_recommended' => $this->isRecommended,
        ];
    }
}
