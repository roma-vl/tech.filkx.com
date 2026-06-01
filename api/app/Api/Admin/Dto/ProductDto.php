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
            nameUk: $request->input('name_uk'),
            nameEn: $request->input('name_en'),
            descriptionUk: $request->input('description_uk'),
            descriptionEn: $request->input('description_en'),
            status: $request->input('status'),
            isHot: (bool) $request->input('is_hot', false),
            isRecommended: (bool) $request->input('is_recommended', false),
            brandId: $request->input('brand_id') ? (int) $request->input('brand_id') : null,
            categoryId: (int) $request->input('category_id'),
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
