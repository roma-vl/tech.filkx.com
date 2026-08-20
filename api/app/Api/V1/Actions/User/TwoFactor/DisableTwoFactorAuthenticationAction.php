<?php

namespace App\Api\V1\Actions\User\TwoFactor;

use App\Api\V1\Dto\AuditLogDto;
use App\Events\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DisableTwoFactorAuthenticationAction
{
    public function execute(User $user, string $password): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        event(new AuditEvent(new AuditLogDto(
            action: 'auth.2fa_disabled',
            domain: 'security',
            message: "User {$user->name} disabled two-factor authentication",
            userId: $user->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        )));
    }
}
