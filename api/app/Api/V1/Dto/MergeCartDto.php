<?php

namespace App\Api\V1\Dto;

class MergeCartDto
{
    public function __construct(
        public readonly string $sessionId
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            sessionId: $request->input('session_id')
        );
    }
}
