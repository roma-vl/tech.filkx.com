<?php

namespace App\Api\Admin\Dto;

class InvoiceFilterDto
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $status
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            search: $request->input('search'),
            status: $request->input('status')
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
        ];
    }
}
