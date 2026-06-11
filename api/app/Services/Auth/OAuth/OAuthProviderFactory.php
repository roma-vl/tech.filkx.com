<?php

namespace App\Services\Auth\OAuth;

use App\Services\Auth\OAuth\Providers\GoogleOAuthProvider;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\Factory as Socialite;

class OAuthProviderFactory
{
    public function __construct(
        private readonly Socialite $socialite
    ) {}

    public function make(string $provider): OAuthProviderInterface
    {
        return match ($provider) {
            'google' => new GoogleOAuthProvider($this->socialite),
            default => throw new InvalidArgumentException("Unsupported OAuth provider: {$provider}"),
        };
    }
}
