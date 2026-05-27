<?php

namespace App\Api\V1\Dto\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AuthToken",
 *     title="Auth Token"
 * )
 */
readonly class AuthTokenDto
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public string $expiresAt,
    ) {}

    /**
     * @OA\Property(property="access_token", type="string")
     * @OA\Property(property="token_type", type="string", example="Bearer")
     * @OA\Property(property="expires_in", type="integer")
     * @OA\Property(property="expires_at", type="string", format="date-time")
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'expires_at' => $this->expiresAt,
        ];
    }
}
