<?php

namespace App\Api\Admin\Dto;

class HomeBannerDto
{
    public function __construct(
        public readonly ?string $badge,
        public readonly ?string $subtitle,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $imagePath,
        public readonly ?string $buttonLabel,
        public readonly string $linkType,
        public readonly ?string $linkValue,
        public readonly bool $isActive,
        public readonly int $sortOrder
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            badge: $request->input('badge'),
            subtitle: $request->input('subtitle'),
            // title is optional (a banner image can already carry its own
            // baked-in text) - the "title" column isn't nullable, so a
            // missing value is coerced to an empty string rather than null.
            title: $request->input('title') ?? '',
            description: $request->input('description'),
            imagePath: $request->input('imagePath'),
            buttonLabel: $request->input('buttonLabel'),
            linkType: $request->input('linkType', 'catalog'),
            linkValue: $request->input('linkValue'),
            isActive: $request->boolean('isActive', true),
            sortOrder: (int) $request->input('sortOrder', 0)
        );
    }

    public function toArray(): array
    {
        return [
            'badge' => $this->badge,
            'subtitle' => $this->subtitle,
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $this->imagePath,
            'button_label' => $this->buttonLabel,
            'link_type' => $this->linkType,
            'link_value' => $this->linkValue,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }
}
