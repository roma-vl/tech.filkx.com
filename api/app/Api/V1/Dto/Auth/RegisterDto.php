<?php

namespace App\Api\V1\Dto\Auth;

readonly class RegisterDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
