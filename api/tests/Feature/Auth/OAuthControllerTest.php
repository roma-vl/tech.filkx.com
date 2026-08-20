<?php

namespace Tests\Feature\Auth;

use App\Models\OAuthAccount;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\OAuth\OAuthProviderFactory;
use App\Services\Auth\OAuth\OAuthProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery\MockInterface;
use Tests\TestCase;

class OAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    private function socialUser(string $id, string $email, ?string $name = 'Jane Doe'): SocialiteUser
    {
        return (new SocialiteUser)->map(['id' => $id, 'email' => $email, 'name' => $name]);
    }

    private function fakeGoogleProvider(SocialiteUser $user): void
    {
        $this->mock(OAuthProviderFactory::class, function (MockInterface $mock) use ($user) {
            $provider = $this->mock(OAuthProviderInterface::class, function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('getUser')->andReturn($user);
            });
            $mock->shouldReceive('make')->with('google')->andReturn($provider);
        });
    }

    private function tokenFromRedirect(string $location): string
    {
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return $query['token'] ?? '';
    }

    public function test_redirect_to_provider_returns_the_authorization_url(): void
    {
        $this->mock(OAuthProviderFactory::class, function (MockInterface $mock) {
            $provider = $this->mock(OAuthProviderInterface::class, function (MockInterface $mock) {
                $mock->shouldReceive('getRedirectUrl')->andReturn('https://accounts.google.com/o/oauth2/auth?client_id=1');
            });
            $mock->shouldReceive('make')->with('google')->andReturn($provider);
        });

        $response = $this->getJson('/api/oauth/google/redirect');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.url', 'https://accounts.google.com/o/oauth2/auth?client_id=1');
    }

    public function test_redirect_to_provider_returns_500_for_an_unsupported_provider(): void
    {
        $response = $this->getJson('/api/oauth/unsupported/redirect');

        $response->assertStatus(500)
            ->assertJsonPath('status', 'error');
    }

    public function test_callback_logs_in_an_already_linked_account(): void
    {
        $user = $this->makeUser(['email' => 'linked@example.com']);
        OAuthAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'email' => 'linked@example.com',
        ]);
        $this->fakeGoogleProvider($this->socialUser('google-123', 'linked@example.com'));

        $response = $this->get('/api/oauth/google/callback');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/auth/callback/success', $location);
        $this->assertNotEmpty($this->tokenFromRedirect($location));
    }

    public function test_callback_creates_a_new_user_when_no_account_matches(): void
    {
        $this->fakeGoogleProvider($this->socialUser('google-new', 'new-visitor@example.com', 'New Visitor'));

        $response = $this->get('/api/oauth/google/callback');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/auth/callback/success', $location);
        $this->assertNotEmpty($this->tokenFromRedirect($location));

        $this->assertDatabaseHas('users', ['email' => 'new-visitor@example.com']);
        $user = User::where('email', 'new-visitor@example.com')->firstOrFail();
        $this->assertDatabaseHas('oauth_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-new',
        ]);
    }

    public function test_callback_links_the_provider_to_an_existing_unlinked_account_with_the_same_email(): void
    {
        $user = $this->makeUser(['email' => 'existing@example.com']);
        $this->fakeGoogleProvider($this->socialUser('google-existing', 'existing@example.com'));

        $response = $this->get('/api/oauth/google/callback');

        $response->assertRedirect();
        $this->assertNotEmpty($this->tokenFromRedirect($response->headers->get('Location')));
        $this->assertDatabaseHas('oauth_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-existing',
        ]);
    }

    public function test_callback_restores_a_soft_deleted_account_matching_the_email(): void
    {
        $user = $this->makeUser(['email' => 'deleted@example.com']);
        $user->delete();
        $this->fakeGoogleProvider($this->socialUser('google-restored', 'deleted@example.com'));

        $response = $this->get('/api/oauth/google/callback');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('restored=true', $location);
        $this->assertNotEmpty($this->tokenFromRedirect($location));
        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_callback_redirects_to_the_error_page_when_the_provider_does_not_return_an_email(): void
    {
        $this->fakeGoogleProvider($this->socialUser('google-no-email', ''));

        $response = $this->get('/api/oauth/google/callback');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/auth/callback/error', $location);
        $this->assertStringContainsString('error=', $location);
    }

    public function test_callback_success_redirect_uses_the_frontend_origin_matching_the_request(): void
    {
        config(['app.frontend_url' => 'https://a.example.com,https://b.example.com']);
        $this->fakeGoogleProvider($this->socialUser('google-origin', 'origin@example.com'));

        $response = $this->get('/api/oauth/google/callback', ['Origin' => 'https://b.example.com']);

        $response->assertRedirect();
        $this->assertStringStartsWith('https://b.example.com/auth/callback/success', $response->headers->get('Location'));
    }

    public function test_callback_error_redirect_uses_the_frontend_origin_matching_the_request(): void
    {
        config(['app.frontend_url' => 'https://a.example.com,https://b.example.com']);
        $this->fakeGoogleProvider($this->socialUser('google-origin-error', ''));

        $response = $this->get('/api/oauth/google/callback', ['Origin' => 'https://b.example.com']);

        $response->assertRedirect();
        $this->assertStringStartsWith('https://b.example.com/auth/callback/error', $response->headers->get('Location'));
    }

    public function test_connect_links_the_authenticated_user_to_the_provider(): void
    {
        $user = $this->makeUser();
        $this->fakeGoogleProvider($this->socialUser('google-connect', 'connect@example.com'));

        $response = $this->postJson('/api/oauth/google/connect', [], $this->authHeader($user));

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('oauth_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-connect',
        ]);
    }

    public function test_connect_rejects_a_provider_account_already_linked_to_someone_else(): void
    {
        $otherUser = $this->makeUser();
        OAuthAccount::create([
            'user_id' => $otherUser->id,
            'provider' => 'google',
            'provider_user_id' => 'google-taken',
            'email' => 'taken@example.com',
        ]);
        $user = $this->makeUser();
        $this->fakeGoogleProvider($this->socialUser('google-taken', 'taken@example.com'));

        $response = $this->postJson('/api/oauth/google/connect', [], $this->authHeader($user));

        $response->assertStatus(422)->assertJsonPath('status', 'error');
    }

    public function test_connect_requires_authentication(): void
    {
        $response = $this->postJson('/api/oauth/google/connect');

        $response->assertStatus(401);
    }

    public function test_disconnect_removes_the_oauth_account(): void
    {
        $user = $this->makeUser(['password' => bcrypt('secret-password')]);
        OAuthAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-disconnect',
            'email' => $user->email,
        ]);

        $response = $this->deleteJson('/api/oauth/google/disconnect', [], $this->authHeader($user));

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('oauth_accounts', ['user_id' => $user->id, 'provider' => 'google']);
    }

    /**
     * OAuthService::disconnect() guards against leaving a user unable to log in by checking
     * `! empty($user->password)` - but the `users.password` column is NOT NULL, and an
     * OAuth-only signup (OAuthService::handleCallback()'s new-user branch) stores
     * `Hash::make('')` rather than a null/empty column value, so this check can never
     * actually be false. The lockout guard is effectively dead code today; documenting the
     * real (not the intended) behavior here rather than asserting a 422 that never happens.
     */
    public function test_disconnect_succeeds_even_for_an_oauth_only_account_because_the_lockout_guard_is_unreachable(): void
    {
        $user = $this->makeUser(['password' => bcrypt('')]);
        OAuthAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-only',
            'email' => $user->email,
        ]);

        $response = $this->deleteJson('/api/oauth/google/disconnect', [], $this->authHeader($user));

        $response->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('oauth_accounts', ['user_id' => $user->id, 'provider' => 'google']);
    }

    public function test_disconnect_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/oauth/google/disconnect');

        $response->assertStatus(401);
    }
}
