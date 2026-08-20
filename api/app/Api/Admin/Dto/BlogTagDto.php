<?php

namespace App\Api\Admin\Dto;

class BlogTagDto
{
    public function __construct(
        public readonly string $nameUk,
        public readonly string $nameEn,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            nameUk: $request->input('nameUk'),
            nameEn: $request->input('nameEn'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => ['uk' => $this->nameUk, 'en' => $this->nameEn],
        ];
    }
}
