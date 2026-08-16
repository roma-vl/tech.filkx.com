<?php

namespace App\Api\V1\Actions\User\TwoFactor;

use App\Api\V1\Dto\AuditLogDto;
use App\Events\AuditEvent;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthenticationAction
{
    public function __construct(
        private readonly TwoFactorAuthenticationService $twoFactor,
    ) {}

    /**
     * @return array<int, string> plaintext recovery codes — shown to the user once, never retrievable again
     */
    public function execute(User $user, string $code): array
    {
        if (! $user->two_factor_secret || $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => ['No pending two-factor enrollment to confirm.'],
            ]);
        }

        if (! $this->twoFactor->verifyCode($user->two_factor_secret, $code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided code is invalid.'],
            ]);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.2fa_enabled',
            domain: 'security',
            message: "User {$user->name} enabled two-factor authentication",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));

        return $recoveryCodes;
    }
}
