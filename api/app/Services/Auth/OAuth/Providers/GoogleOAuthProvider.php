<?php

namespace App\Services\Auth\OAuth\Providers;

use App\Services\Auth\OAuth\OAuthProviderInterface;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleOAuthProvider implements OAuthProviderInterface
{
    public function __construct(
        private readonly Socialite $socialite
    ) {}

    public function getRedirectUrl(): string
    {
        $attribution = request()->cookie('saas_attribution');
        // We pass the attribution cookie value in the 'state' parameter
        // encoded to ensure it survives the round trip.
        // Note: Socialite handles CSRF state automatically, but we can append data if needed
        // or rely on the cookie surviving if SameSite=Lax/None.
        // Ideally, we should merge this with Socialite's state, but Socialite usually manages state internally.
        // A safer way for stateless APIs is to trust the cookie presence on callback IF domain is correct.
        // However, if we want to be 100% sure, we can use 'with' to add custom parameters.

        $driver = $this->socialite->driver('google')->stateless();

        if ($attribution) {
            $driver->with(['state' => base64_encode(json_encode(['attribution' => $attribution]))]);
        }

        return $driver->redirect()->getTargetUrl();
    }

    public function getUser(): SocialiteUser
    {
        return $this->socialite->driver('google')->stateless()->user();
    }
}
