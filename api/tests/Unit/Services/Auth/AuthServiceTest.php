<?php

namespace Tests\Unit\Services\Auth;

use App\Api\V1\Dto\Auth\ForgotPasswordDto;
use App\Api\V1\Dto\Auth\LoginDto;
use App\Api\V1\Dto\Auth\RegisterDto;
use App\Api\V1\Dto\Auth\ResetPasswordDto;
use App\Api\V1\Services\AuthService;
use App\Events\AuditEvent;
use App\Models\Role;
use App\Models\User;
use App\Notifications\LoginNewDeviceNotification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Token;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private const TWO_FACTOR_CHALLENGE_CACHE_PREFIX = 'auth.2fa_challenge.';

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AuthService::class);
        $this->fakeRequest();
    }

    private function fakeRequest(string $ip = '127.0.0.1', string $userAgent = 'PHPUnit-Agent'): void
    {
        $request = Request::create('/', 'POST', server: [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => $userAgent,
        ]);

        $this->app->instance('request', $request);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    private function currentCodeFor(string $secret): string
    {
        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    /**
     * @return array{secret: string, recoveryCodes: array<int, string>}
     */
    private function enableTwoFactor(User $user): array
    {
        $twoFactor = app(TwoFactorAuthenticationService::class);
        $secret = $twoFactor->generateSecretKey();
        $recoveryCodes = $twoFactor->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        return ['secret' => $secret, 'recoveryCodes' => $recoveryCodes];
    }

    /**
     * Issues a real Passport token for the user and wires it up as their "current" token,
     * the way the auth:api guard would after resolving a bearer token from a request.
     *
     * @return array{token: Token, accessToken: string}
     */
    private function issueCurrentToken(User $user): array
    {
        $tokenResult = $user->createToken('api-access');
        $user->withAccessToken(new AccessToken(['oauth_access_token_id' => $tokenResult->token->id]));

        return ['token' => $tokenResult->token, 'accessToken' => $tokenResult->accessToken];
    }

    // ---- register ----

    public function test_register_creates_user_with_user_role_and_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $this->service->register(new RegisterDto('John Doe', 'john@example.com', 'password123'));

        $user = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertTrue($user->hasAnyRole(['user']));
        Event::assertDispatched(Registered::class, fn ($event) => $event->user->is($user));
    }

    public function test_register_sends_email_verification_notification(): void
    {
        Notification::fake();

        $this->service->register(new RegisterDto('Jane Doe', 'jane@example.com', 'password123'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_register_returns_token_and_user_resource_and_issues_a_real_access_token(): void
    {
        $result = $this->service->register(new RegisterDto('Alice', 'alice@example.com', 'password123'));

        $user = User::where('email', 'alice@example.com')->firstOrFail();

        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertNotEmpty($result['token']['accessToken']);
        $this->assertSame($user->id, $result['user']->resource->id);
        $this->assertSame('Registration successful. Please verify your email.', $result['message']);
        $this->assertDatabaseHas('oauth_access_tokens', ['user_id' => $user->id, 'revoked' => false]);
    }

    // ---- login ----

    public function test_login_with_correct_credentials_issues_token_and_returns_user(): void
    {
        $user = $this->makeUser(['email' => 'login@example.com', 'password' => Hash::make('secret123')]);

        $result = $this->service->login(new LoginDto('login@example.com', 'secret123'));

        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertSame($user->id, $result['user']->resource->id);
        $this->assertDatabaseHas('oauth_access_tokens', ['user_id' => $user->id, 'revoked' => false]);
    }

    public function test_login_with_wrong_password_throws_validation_exception_with_expected_message(): void
    {
        $this->makeUser(['email' => 'login2@example.com', 'password' => Hash::make('secret123')]);

        try {
            $this->service->login(new LoginDto('login2@example.com', 'wrong-password'));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame('The provided credentials are incorrect.', $e->errors()['email'][0]);
        }
    }

    public function test_login_with_wrong_password_fires_failed_login_audit_event(): void
    {
        Event::fake([AuditEvent::class]);
        $this->makeUser(['email' => 'login3@example.com', 'password' => Hash::make('secret123')]);

        try {
            $this->service->login(new LoginDto('login3@example.com', 'wrong-password'));
        } catch (ValidationException) {
            // expected — assertions happen below
        }

        Event::assertDispatched(AuditEvent::class, fn ($event) => $event->dto->action === 'auth.failed');
    }

    public function test_login_for_two_factor_enabled_user_returns_challenge_and_creates_no_token(): void
    {
        $user = $this->makeUser(['email' => 'twofa@example.com', 'password' => Hash::make('secret123')]);
        $this->enableTwoFactor($user);

        $result = $this->service->login(new LoginDto('twofa@example.com', 'secret123'));

        $this->assertTrue($result['two_factor_required']);
        $this->assertNotEmpty($result['challenge_token']);
        $this->assertArrayNotHasKey('token', $result);

        $this->assertTrue(Cache::has(self::TWO_FACTOR_CHALLENGE_CACHE_PREFIX.$result['challenge_token']));
        $this->assertDatabaseMissing('oauth_access_tokens', ['user_id' => $user->id]);
    }

    // ---- logout ----

    public function test_logout_revokes_current_token_and_fires_audit_event(): void
    {
        Event::fake([AuditEvent::class]);
        $user = $this->makeUser();
        $issued = $this->issueCurrentToken($user);

        $this->service->logout($user);

        $this->assertTrue($issued['token']->fresh()->revoked);
        Event::assertDispatched(AuditEvent::class, fn ($event) => $event->dto->action === 'auth.logout');
    }

    // ---- refreshToken ----

    public function test_refresh_token_issues_new_token_and_revokes_old_one(): void
    {
        $user = $this->makeUser();
        $issued = $this->issueCurrentToken($user);

        $result = $this->service->refreshToken($user);

        $this->assertTrue($issued['token']->fresh()->revoked);
        $this->assertNotSame($issued['accessToken'], $result['token']['accessToken']);
    }

    public function test_refresh_token_on_a_normal_non_impersonated_token_does_not_throw(): void
    {
        // Regression test: refreshToken() used to unconditionally read
        // oauth_access_tokens.impersonator_id, a column that does not exist in any
        // migration, which crashed with an "Undefined property" error for every normal
        // (non-impersonated) refresh. It is now guarded with isset().
        $user = $this->makeUser();
        $this->issueCurrentToken($user);

        $result = $this->service->refreshToken($user);

        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertNotEmpty($result['token']['accessToken']);
    }

    // ---- verifyEmail ----

    public function test_verify_email_with_valid_signed_params_marks_user_verified(): void
    {
        $user = $this->makeUser(['email_verified_at' => null]);

        $message = $this->service->verifyEmail(
            (string) $user->id,
            sha1($user->email),
            (string) now()->addMinutes(60)->timestamp,
            'irrelevant-for-current-implementation'
        );

        $this->assertSame('Email verified successfully', $message);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_email_when_already_verified_returns_message_without_erroring(): void
    {
        $user = $this->makeUser();

        $message = $this->service->verifyEmail(
            (string) $user->id,
            sha1($user->email),
            (string) now()->addMinutes(60)->timestamp,
            'irrelevant-for-current-implementation'
        );

        $this->assertSame('Email already verified', $message);
    }

    public function test_verify_email_with_tampered_hash_throws_validation_exception(): void
    {
        $user = $this->makeUser(['email_verified_at' => null]);

        $this->expectException(ValidationException::class);

        $this->service->verifyEmail(
            (string) $user->id,
            sha1('someone-else@example.com'),
            (string) now()->addMinutes(60)->timestamp,
            'irrelevant-for-current-implementation'
        );
    }

    // ---- resendVerificationEmail ----

    public function test_resend_verification_email_sends_notification_for_unverified_user(): void
    {
        Notification::fake();
        $user = $this->makeUser(['email' => 'unverified@example.com', 'email_verified_at' => null]);

        $this->service->resendVerificationEmail('unverified@example.com');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_verification_email_throws_for_already_verified_user(): void
    {
        $user = $this->makeUser(['email' => 'verified@example.com']);

        $this->expectException(ValidationException::class);

        $this->service->resendVerificationEmail('verified@example.com');
    }

    // ---- sendResetLink ----

    public function test_send_reset_link_for_known_email_delegates_to_password_broker(): void
    {
        Notification::fake();
        $user = $this->makeUser(['email' => 'forgot@example.com']);

        $this->service->sendResetLink(new ForgotPasswordDto('forgot@example.com'));

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_send_reset_link_for_unknown_email_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->sendResetLink(new ForgotPasswordDto('unknown@example.com'));
    }

    // ---- resetPassword ----

    public function test_reset_password_updates_hash_and_revokes_all_existing_tokens(): void
    {
        $user = $this->makeUser(['email' => 'reset@example.com']);
        $user->createToken('api-access');
        $user->createToken('api-access-2');
        $token = Password::broker()->createToken($user);

        $this->service->resetPassword(new ResetPasswordDto('reset@example.com', 'newpassword123', $token));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_reset_password_with_invalid_token_throws_validation_exception(): void
    {
        $user = $this->makeUser(['email' => 'reset2@example.com']);
        $originalPasswordHash = $user->password;

        try {
            $this->service->resetPassword(new ResetPasswordDto('reset2@example.com', 'newpassword123', 'not-a-real-token'));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame($originalPasswordHash, $user->fresh()->password);
    }

    // ---- createLoginResponse ----

    public function test_create_login_response_issues_token_and_fires_oauth_login_audit_event(): void
    {
        Event::fake([AuditEvent::class]);
        $user = $this->makeUser();

        $result = $this->service->createLoginResponse($user);

        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertDatabaseHas('oauth_access_tokens', ['user_id' => $user->id, 'revoked' => false]);
        Event::assertDispatched(AuditEvent::class, fn ($event) => $event->dto->action === 'auth.login_oauth');
    }

    public function test_create_login_response_sends_new_device_notification(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->service->createLoginResponse($user);

        Notification::assertSentTo($user, LoginNewDeviceNotification::class);
    }

    public function test_create_login_response_for_two_factor_enabled_user_returns_challenge(): void
    {
        $user = $this->makeUser();
        $this->enableTwoFactor($user);

        $result = $this->service->createLoginResponse($user);

        $this->assertTrue($result['two_factor_required']);
        $this->assertArrayNotHasKey('token', $result);
    }

    // ---- verifyTwoFactorChallenge ----

    public function test_verify_two_factor_challenge_with_valid_totp_code_completes_login(): void
    {
        $user = $this->makeUser(['email' => 'tfa1@example.com', 'password' => Hash::make('secret123')]);
        $enrollment = $this->enableTwoFactor($user);
        $challengeToken = $this->service->login(new LoginDto('tfa1@example.com', 'secret123'))['challenge_token'];

        $result = $this->service->verifyTwoFactorChallenge($challengeToken, $this->currentCodeFor($enrollment['secret']));

        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertDatabaseHas('oauth_access_tokens', ['user_id' => $user->id, 'revoked' => false]);
    }

    public function test_verify_two_factor_challenge_with_valid_recovery_code_completes_login_and_burns_it(): void
    {
        $user = $this->makeUser(['email' => 'tfa2@example.com', 'password' => Hash::make('secret123')]);
        $enrollment = $this->enableTwoFactor($user);
        $recoveryCode = $enrollment['recoveryCodes'][0];

        $challengeToken = $this->service->login(new LoginDto('tfa2@example.com', 'secret123'))['challenge_token'];
        $result = $this->service->verifyTwoFactorChallenge($challengeToken, $recoveryCode);

        $this->assertArrayHasKey('accessToken', $result['token']);
        $this->assertNotContains($recoveryCode, $user->fresh()->two_factor_recovery_codes);

        // A fresh challenge with the same (already burned) recovery code must now fail.
        $secondChallengeToken = $this->service->login(new LoginDto('tfa2@example.com', 'secret123'))['challenge_token'];

        $this->expectException(ValidationException::class);
        $this->service->verifyTwoFactorChallenge($secondChallengeToken, $recoveryCode);
    }

    public function test_verify_two_factor_challenge_with_unknown_token_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->verifyTwoFactorChallenge('not-a-real-challenge-token', '000000');
    }

    public function test_verify_two_factor_challenge_is_single_use(): void
    {
        $user = $this->makeUser(['email' => 'tfa3@example.com', 'password' => Hash::make('secret123')]);
        $enrollment = $this->enableTwoFactor($user);
        $challengeToken = $this->service->login(new LoginDto('tfa3@example.com', 'secret123'))['challenge_token'];

        $this->service->verifyTwoFactorChallenge($challengeToken, $this->currentCodeFor($enrollment['secret']));

        $this->assertFalse(Cache::has(self::TWO_FACTOR_CHALLENGE_CACHE_PREFIX.$challengeToken));

        $this->expectException(ValidationException::class);
        $this->service->verifyTwoFactorChallenge($challengeToken, $this->currentCodeFor($enrollment['secret']));
    }

    // ---- notifyIfNewDevice (exercised via login()) ----

    public function test_login_sends_new_device_notification_on_first_login_from_ip_and_user_agent(): void
    {
        Notification::fake();
        $user = $this->makeUser(['email' => 'device1@example.com', 'password' => Hash::make('secret123')]);
        $this->fakeRequest('11.22.33.44', 'Custom-UA/1.0');

        $this->service->login(new LoginDto('device1@example.com', 'secret123'));

        Notification::assertSentTo($user, LoginNewDeviceNotification::class);
    }

    public function test_login_does_not_resend_new_device_notification_for_same_ip_and_user_agent(): void
    {
        $user = $this->makeUser(['email' => 'device2@example.com', 'password' => Hash::make('secret123')]);
        $this->fakeRequest('55.66.77.88', 'Repeat-UA/1.0');

        $this->service->login(new LoginDto('device2@example.com', 'secret123'));

        Notification::fake();
        $this->service->login(new LoginDto('device2@example.com', 'secret123'));

        Notification::assertNotSentTo($user, LoginNewDeviceNotification::class);
    }

    // ---- parseDeviceName (exercised via the new-device notification) ----

    public function test_new_device_notification_contains_a_readable_device_name_for_a_known_user_agent(): void
    {
        Notification::fake();
        $user = $this->makeUser(['email' => 'device3@example.com', 'password' => Hash::make('secret123')]);
        $this->fakeRequest(
            '77.88.99.11',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        $this->service->login(new LoginDto('device3@example.com', 'secret123'));

        Notification::assertSentTo($user, LoginNewDeviceNotification::class, function (LoginNewDeviceNotification $notification) {
            return str_contains($notification->deviceName, 'Chrome')
                && str_contains($notification->deviceName, 'Windows');
        });
    }
}
