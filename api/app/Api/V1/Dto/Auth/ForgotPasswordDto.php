<?php

namespace App\Api\V1\Dto\Auth;

readonly class ForgotPasswordDto
{
    public function __construct(
        public string $email,
    ) {}
}
