<?php

namespace App\Api\V1\Dto\Auth;

readonly class ResetPasswordDto
{
    public function __construct(
        public string $email,
        public string $password,
        public string $token,
    ) {}
}
