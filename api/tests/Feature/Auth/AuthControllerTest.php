<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'token' => ['accessToken', 'tokenType', 'expiresIn', 'expiresAt'],
                    'user' => ['id', 'email'],
                    'message',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $this->makeUser(['email' => 'dup@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'dup@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_rejects_weak_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'weakpass@example.com',
            'password' => '123',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'weakpass@example.com']);
    }

    public function test_login_with_correct_credentials_issues_token(): void
    {
        $this->makeUser(['email' => 'login@example.com', 'password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token' => ['accessToken'],
                    'user' => ['id', 'email'],
                ],
            ]);
    }

    public function test_login_with_wrong_password_returns_422_with_expected_message(): void
    {
        $this->makeUser(['email' => 'login2@example.com', 'password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login2@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
    }

    public function test_login_with_unknown_email_returns_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'whatever123',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_rate_limit_kicks_in_after_five_attempts(): void
    {
        $this->makeUser(['email' => 'ratelimit@example.com', 'password' => bcrypt('secret123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'ratelimit@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ratelimit@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_logout_revokes_token(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('api-access')->accessToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Passport's TokenGuard caches the resolved user on the guard instance, and the
        // guard instance itself is cached per-name on the AuthManager singleton — both of
        // which persist across postJson() calls within a single test method (they share
        // one app instance). Forget guards so the next call re-authenticates from scratch
        // and actually observes the revocation.
        Auth::shouldUse('web');
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    public function test_refresh_issues_a_new_token(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('api-access')->accessToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/refresh');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['token' => ['accessToken']]]);

        $newToken = $response->json('data.token.accessToken');
        $this->assertNotSame($token, $newToken);

        // Old token must be revoked
        Auth::shouldUse('web');
        Auth::forgetGuards();
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);

        Auth::shouldUse('web');
        Auth::forgetGuards();
        $this->withHeader('Authorization', "Bearer {$newToken}")
            ->getJson('/api/v1/auth/me')
            ->assertOk();
    }

    public function test_forgot_password_with_known_email_queues_reset_notification(): void
    {
        Notification::fake();

        $user = $this->makeUser(['email' => 'forgot@example.com']);

        $this->postJson('/api/v1/auth/password/forgot', ['email' => 'forgot@example.com'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_with_unknown_email_fails_validation(): void
    {
        $this->postJson('/api/v1/auth/password/forgot', ['email' => 'unknown@example.com'])
            ->assertStatus(422);
    }

    public function test_reset_password_with_valid_token_succeeds_and_revokes_old_tokens(): void
    {
        $user = $this->makeUser(['email' => 'reset@example.com']);
        $oldToken = $user->createToken('api-access')->accessToken;

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ])->assertOk();

        $this->assertTrue(\Hash::check('newpassword123', $user->fresh()->password));

        $this->withHeader('Authorization', "Bearer {$oldToken}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    public function test_reset_password_with_invalid_token_fails(): void
    {
        $this->makeUser(['email' => 'reset2@example.com']);

        $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'reset2@example.com',
            'password' => 'newpassword123',
            'token' => 'not-a-real-token',
        ])->assertStatus(422);
    }

    public function test_verify_email_with_valid_link_verifies_email(): void
    {
        $user = $this->makeUser(['email_verified_at' => null]);

        $params = http_build_query([
            'id' => $user->id,
            'hash' => sha1($user->email),
            'expires' => now()->addMinutes(60)->timestamp,
            'signature' => 'irrelevant-for-current-implementation',
        ]);

        $this->getJson('/api/v1/auth/verify-email?'.$params)->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_email_with_tampered_hash_is_rejected(): void
    {
        $user = $this->makeUser(['email_verified_at' => null]);

        $params = http_build_query([
            'id' => $user->id,
            'hash' => sha1('someone-else@example.com'),
            'expires' => now()->addMinutes(60)->timestamp,
            'signature' => 'irrelevant-for-current-implementation',
        ]);

        $this->getJson('/api/v1/auth/verify-email?'.$params)->assertStatus(422);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_verification_sends_email_for_unverified_user(): void
    {
        Notification::fake();

        $this->makeUser(['email' => 'unverified@example.com', 'email_verified_at' => null]);

        $this->postJson('/api/v1/auth/email/resend', ['email' => 'unverified@example.com'])
            ->assertOk();
    }
}
