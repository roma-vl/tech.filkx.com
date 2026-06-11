<?php

namespace App\Services\Auth\OAuth;

use Laravel\Socialite\Contracts\User as SocialiteUser;

interface OAuthProviderInterface
{
    public function getRedirectUrl(): string;

    public function getUser(): SocialiteUser;
}
