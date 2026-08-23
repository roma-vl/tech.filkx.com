<?php

namespace App\Api\V1\Actions\User\TwoFactor;

use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Validation\ValidationException;

class EnableTwoFactorAuthenticationAction
{
    public function __construct(
        private readonly TwoFactorAuthenticationService $twoFactor,
    ) {}

    /**
     * Starts (or restarts) enrollment: generates a new secret and stores it unconfirmed.
     * 2FA is not enforced on login until ConfirmTwoFactorAuthenticationAction succeeds.
     *
     * @return array{secret: string, qr_code_url: string}
     */
    public function execute(User $user): array
    {
        if ($user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => ['Two-factor authentication is already enabled. Disable it first to re-enroll.'],
            ]);
        }

        $secret = $this->twoFactor->generateSecretKey();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return [
            'secret' => $secret,
            'qr_code_url' => $this->twoFactor->getQrCodeUrl($user->email, $secret),
        ];
    }
}
