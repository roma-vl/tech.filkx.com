<?php

namespace App\Services\Auth\OAuth;

use App\Api\V1\Services\AuthService;
use App\Models\OAuthAccount;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Reuse login logic
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OAuthService
{
    public function __construct(
        private readonly OAuthProviderFactory $factory,
        private readonly AuthService $authService
    ) {}

    public function getRedirectUrl(string $provider): string
    {
        return $this->factory->make($provider)->getRedirectUrl();
    }

    /**
     * Handle OAuth callback - main authentication logic
     *
     * Cases:
     * A. OAuth account exists -> Login user
     * B. Email exists but OAuth not linked -> Return 409 conflict
     * C. New user -> Create user and OAuth account
     *
     * @throws Exception
     */
    public function handleCallback(string $provider, Request $request): array
    {
        $socialUser = $this->factory->make($provider)->getUser();
        $email = $socialUser->getEmail();
        $providerUserId = $socialUser->getId();

        if (! $email) {
            throw new Exception('Email not provided by OAuth provider', 400);
        }

        $oauthAccount = OAuthAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->whereHas('user', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->first();

        if ($oauthAccount) {
            if (! $oauthAccount->user->hasVerifiedEmail()) {
                $oauthAccount->user->update(['email_verified_at' => now()]);
            }

            return $this->authService->createLoginResponse($oauthAccount->user);
        }

        $existingUser = User::where('email', $email)->whereNull('deleted_at')->first();

        if (! $existingUser) {
            $deletedUser = User::where('email', $email)->whereNotNull('deleted_at')->withTrashed()->first();

            if ($deletedUser) {
                \DB::table('users')
                    ->where('id', $deletedUser->id)
                    ->update([
                        'deleted_at' => null,
                        'email_verified_at' => now(),
                    ]);

                $deletedUser = $deletedUser->fresh();
                $response = $this->authService->createLoginResponse($deletedUser);
                $response['account_restored'] = true;

                return $response;
            }
        }

        if ($existingUser) {
            if (Auth::check() && Auth::id() === $existingUser->id && ! $existingUser->deleted_at) {
                OAuthAccount::create([
                    'user_id' => $existingUser->id,
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                    'email' => $email,
                ]);

                if (! $existingUser->hasVerifiedEmail()) {
                    $existingUser->update(['email_verified_at' => now()]);
                }

                return $this->authService->createLoginResponse($existingUser);
            }

            if ($existingUser->email === $email) {
                OAuthAccount::create([
                    'user_id' => $existingUser->id,
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                    'email' => $email,
                ]);

                if (! $existingUser->hasVerifiedEmail()) {
                    $existingUser->update(['email_verified_at' => now()]);
                }

                return $this->authService->createLoginResponse($existingUser);
            }

            throw new RuntimeException(
                __('This email account is no longer available. Please contact support if you need assistance.'),
                409
            );
        }

        $deletedUser = User::where('email', $email)->whereNotNull('deleted_at')->withTrashed()->first();
        if ($deletedUser) {
            $deletedUser->forceFill([
                'deleted_at' => null,
                'email_verified_at' => now(),
            ])->save();

            OAuthAccount::create([
                'user_id' => $deletedUser->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'email' => $email,
            ]);

            return $this->authService->createLoginResponse($deletedUser);
        }

        return DB::transaction(function () use ($socialUser, $provider, $providerUserId, $email) {
            $user = User::create([
                'name' => $socialUser->getName() ?? explode('@', $email)[0],
                'email' => $email,
                'password' => Hash::make(''),
                'email_verified_at' => now(),
                'locale' => User::DEFAULT_LOCALE,
                'timezone' => 'Europe/Kyiv',
            ]);

            $userRole = Role::where('slug', 'user')->firstOrFail();
            $user->roles()->attach($userRole->id);

            OAuthAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'email' => $email,
            ]);

            return $this->authService->createLoginResponse($user);
        });
    }

    /**
     * Connect existing user account to OAuth provider
     * Used for linking OAuth to already authenticated users
     *
     * @throws Exception
     */
    public function connect(User $user, string $provider): void
    {
        $socialUser = $this->factory->make($provider)->getUser();

        $exists = OAuthAccount::where('provider', $provider)
            ->where('provider_user_id', $socialUser->getId())
            ->exists();

        if ($exists) {
            throw new RuntimeException("This {$provider} account is already connected to another user.", 422);
        }

        $alreadyConnected = $user->oauthAccounts()->where('provider', $provider)->exists();
        if ($alreadyConnected) {
            throw new RuntimeException("You already have a {$provider} account connected.", 422);
        }

        OAuthAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
        ]);
    }

    /**
     * Disconnect OAuth account from user
     * Prevents users from becoming locked out by ensuring they have at least one authentication method
     */
    public function disconnect(User $user, string $provider): void
    {
        $remainingOAuthCount = $user->oauthAccounts()
            ->where('provider', '!=', $provider)
            ->count();

        $hasPassword = ! empty($user->password);

        if ($remainingOAuthCount === 0 && ! $hasPassword) {
            throw ValidationException::withMessages([
                'provider' => ['To disconnect '.ucfirst($provider).', you must set a password first.'],
            ]);
        }

        $user->oauthAccounts()->where('provider', $provider)->delete();
    }
}
