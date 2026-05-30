<?php

namespace App\Api\Admin\Dto;

class MarketingFilterDto
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $status,
        public readonly string $sortBy,
        public readonly string $sortDir
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            search: $request->input('search'),
            status: $request->input('status'),
            sortBy: $request->input('sortBy', 'created_at'),
            sortDir: $request->input('sortDir', 'desc')
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'sortBy' => $this->sortBy,
            'sortDir' => $this->sortDir,
        ];
    }
}
