<?php

namespace App\Services\Auth;

use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationService
{
    private const RECOVERY_CODE_COUNT = 10;

    public function __construct(
        private readonly Google2FA $google2fa,
    ) {}

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrCodeUrl(string $accountName, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name', 'Filkx'),
            $accountName,
            $secret,
        );
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)))
            ->all();
    }
}
