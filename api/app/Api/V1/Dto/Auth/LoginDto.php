<?php

namespace App\Api\V1\Dto\Auth;

readonly class LoginDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
