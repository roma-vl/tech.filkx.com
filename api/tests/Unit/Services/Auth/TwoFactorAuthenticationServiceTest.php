<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationServiceTest extends TestCase
{
    private function service(): TwoFactorAuthenticationService
    {
        return new TwoFactorAuthenticationService(new Google2FA);
    }

    public function test_generate_secret_key_returns_a_non_empty_string(): void
    {
        $secret = $this->service()->generateSecretKey();

        $this->assertIsString($secret);
        $this->assertNotEmpty($secret);
    }

    public function test_get_qr_code_url_returns_an_otpauth_uri_containing_account_name_and_secret(): void
    {
        $service = $this->service();
        $secret = $service->generateSecretKey();

        $url = $service->getQrCodeUrl('user@example.com', $secret);

        $this->assertStringStartsWith('otpauth://totp/', $url);
        $this->assertStringContainsString(rawurlencode('user@example.com'), $url);
        $this->assertStringContainsString($secret, $url);
    }

    public function test_verify_code_accepts_a_currently_valid_totp_code(): void
    {
        $service = $this->service();
        $secret = $service->generateSecretKey();
        $currentCode = (new Google2FA)->getCurrentOtp($secret);

        $this->assertTrue($service->verifyCode($secret, $currentCode));
    }

    public function test_verify_code_rejects_an_invalid_code(): void
    {
        $service = $this->service();
        $secret = $service->generateSecretKey();

        $this->assertFalse($service->verifyCode($secret, '000000'));
    }

    public function test_generate_recovery_codes_returns_ten_unique_formatted_codes(): void
    {
        $codes = $this->service()->generateRecoveryCodes();

        $this->assertCount(10, $codes);
        $this->assertCount(10, array_unique($codes));

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code);
        }
    }
}
