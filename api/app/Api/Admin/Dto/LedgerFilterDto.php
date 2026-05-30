<?php

namespace App\Api\Admin\Dto;

class LedgerFilterDto
{
    public function __construct(
        public readonly ?int $userId,
        public readonly ?string $type,
        public readonly ?string $from,
        public readonly ?string $to
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            userId: $request->input('user_id') ? (int) $request->input('user_id') : null,
            type: $request->input('type'),
            from: $request->input('from'),
            to: $request->input('to')
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'type' => $this->type,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
