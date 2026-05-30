<?php

namespace App\Api\V1\Dto;

class CartSessionDto
{
    public function __construct(
        public readonly ?int $userId,
        public readonly ?string $sessionId
    ) {}

    public static function fromRequest($request): self
    {
        $user = auth('api')->user();

        return new self(
            userId: $user ? $user->id : null,
            sessionId: $request->header('X-Cart-Session-ID') ?: $request->input('session_id')
        );
    }
}
