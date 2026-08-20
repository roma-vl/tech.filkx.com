<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationControllerTest extends TestCase
{
    use RefreshDatabase;

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

    private function enrollTwoFactor(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        $enableResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/user/2fa/enable');

        $secret = $enableResponse->json('data.secret');

        $confirmResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/user/2fa/confirm', ['code' => $this->currentCodeFor($secret)]);

        // The `auth:api` middleware calls Auth::shouldUse('api') on successful
        // authentication, which overwrites the `auth.defaults.guard` config value, and
        // guard instances are cached per-name on the AuthManager singleton. Both would
        // otherwise leak into later calls made within the same test method, since the app
        // instance (and therefore the AuthManager) is shared across postJson() calls
        // within one test.
        Auth::shouldUse('web');
        Auth::forgetGuards();

        return [
            'token' => $token,
            'secret' => $secret,
            'recoveryCodes' => $confirmResponse->json('data.recoveryCodes'),
        ];
    }

    public function test_enable_returns_secret_and_qr_code_url(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('api-access')->accessToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/user/2fa/enable');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['secret', 'qrCodeUrl']]);

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_confirm_with_correct_code_enables_two_factor_and_returns_recovery_codes(): void
    {
        $user = $this->makeUser();
        $result = $this->enrollTwoFactor($user);

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertCount(10, $result['recoveryCodes']);
    }

    public function test_confirm_with_wrong_code_fails(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('api-access')->accessToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/user/2fa/enable');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/user/2fa/confirm', ['code' => '000000'])
            ->assertStatus(422);

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_login_for_two_factor_enabled_user_returns_two_factor_required(): void
    {
        $user = $this->makeUser(['email' => 'twofa@example.com', 'password' => bcrypt('secret123')]);
        $this->enrollTwoFactor($user);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'twofa@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.twoFactorRequired', true)
            ->assertJsonStructure(['data' => ['challengeToken']]);

        $this->assertArrayNotHasKey('token', $response->json('data'));
    }

    public function test_verify_with_correct_totp_code_completes_login(): void
    {
        $user = $this->makeUser(['email' => 'twofa2@example.com', 'password' => bcrypt('secret123')]);
        $enrollment = $this->enrollTwoFactor($user);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'twofa2@example.com',
            'password' => 'secret123',
        ]);
        $challengeToken = $loginResponse->json('data.challengeToken');

        $response = $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $challengeToken,
            'code' => $this->currentCodeFor($enrollment['secret']),
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token' => ['accessToken'], 'user' => ['id']]]);
    }

    public function test_verify_with_valid_recovery_code_completes_login_and_burns_it(): void
    {
        $user = $this->makeUser(['email' => 'twofa3@example.com', 'password' => bcrypt('secret123')]);
        $enrollment = $this->enrollTwoFactor($user);
        $recoveryCode = $enrollment['recoveryCodes'][0];

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'twofa3@example.com',
            'password' => 'secret123',
        ]);
        $challengeToken = $loginResponse->json('data.challengeToken');

        $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $challengeToken,
            'code' => $recoveryCode,
        ])->assertOk();

        // Reusing the same recovery code against a fresh challenge must fail.
        $secondLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'twofa3@example.com',
            'password' => 'secret123',
        ]);

        $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $secondLogin->json('data.challengeToken'),
            'code' => $recoveryCode,
        ])->assertStatus(422);
    }

    public function test_verify_with_wrong_code_fails(): void
    {
        $user = $this->makeUser(['email' => 'twofa4@example.com', 'password' => bcrypt('secret123')]);
        $this->enrollTwoFactor($user);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'twofa4@example.com',
            'password' => 'secret123',
        ]);

        $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $loginResponse->json('data.challengeToken'),
            'code' => '000000',
        ])->assertStatus(422);
    }

    public function test_disable_requires_correct_password(): void
    {
        $user = $this->makeUser(['password' => bcrypt('secret123')]);
        $enrollment = $this->enrollTwoFactor($user);

        $this->withHeader('Authorization', "Bearer {$enrollment['token']}")
            ->postJson('/api/user/2fa/disable', ['password' => 'wrong-password'])
            ->assertStatus(422);

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->withHeader('Authorization', "Bearer {$enrollment['token']}")
            ->postJson('/api/user/2fa/disable', ['password' => 'secret123'])
            ->assertOk();

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_regenerate_requires_valid_code_and_invalidates_old_codes(): void
    {
        $user = $this->makeUser();
        $enrollment = $this->enrollTwoFactor($user);
        $oldCodes = $enrollment['recoveryCodes'];

        $this->withHeader('Authorization', "Bearer {$enrollment['token']}")
            ->postJson('/api/user/2fa/recovery-codes/regenerate', ['code' => '000000'])
            ->assertStatus(422);

        $response = $this->withHeader('Authorization', "Bearer {$enrollment['token']}")
            ->postJson('/api/user/2fa/recovery-codes/regenerate', [
                'code' => $this->currentCodeFor($enrollment['secret']),
            ]);

        $response->assertOk();
        $newCodes = $response->json('data.recoveryCodes');

        $this->assertCount(10, $newCodes);
        $this->assertNotSame($oldCodes, $newCodes);
        $this->assertEmpty(array_intersect($oldCodes, $newCodes));
    }
}
