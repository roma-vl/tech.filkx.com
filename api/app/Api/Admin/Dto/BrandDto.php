<?php

namespace App\Api\Admin\Dto;

use Illuminate\Support\Str;

class BrandDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $logoPath,
        public readonly ?string $description
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->input('name'),
            slug: Str::slug($request->input('name')),
            logoPath: $request->input('logoPath'),
            description: $request->input('description')
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_path' => $this->logoPath,
            'description' => $this->description,
        ];
    }
}
