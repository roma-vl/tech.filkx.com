<?php

namespace App\Api\Admin\Dto;

class PromoPageDto
{
    public function __construct(
        public readonly ?string $badge,
        public readonly string $title,
        public readonly ?string $subtitle,
        public readonly ?string $description,
        public readonly string $imagePath,
        public readonly bool $isActive,
        public readonly int $sortOrder,
        public readonly array $productIds
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            badge: $request->input('badge'),
            title: $request->input('title'),
            subtitle: $request->input('subtitle'),
            description: $request->input('description'),
            imagePath: $request->input('imagePath'),
            isActive: $request->boolean('isActive', true),
            sortOrder: (int) $request->input('sortOrder', 0),
            productIds: $request->input('productIds', [])
        );
    }

    public function toArray(): array
    {
        return [
            'badge' => $this->badge,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'image_path' => $this->imagePath,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }
}
