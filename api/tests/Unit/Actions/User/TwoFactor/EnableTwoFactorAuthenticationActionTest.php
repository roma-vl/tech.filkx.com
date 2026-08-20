<?php

namespace Tests\Unit\Actions\User\TwoFactor;

use App\Api\V1\Actions\User\TwoFactor\EnableTwoFactorAuthenticationAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EnableTwoFactorAuthenticationActionTest extends TestCase
{
    use RefreshDatabase;

    private EnableTwoFactorAuthenticationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(EnableTwoFactorAuthenticationAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_generates_and_stores_an_unconfirmed_secret(): void
    {
        $user = $this->makeUser();

        $result = $this->action->execute($user);

        $this->assertNotEmpty($result['secret']);
        $this->assertStringStartsWith('otpauth://totp/', $result['qr_code_url']);
        $this->assertStringContainsString($result['secret'], $result['qr_code_url']);

        $fresh = $user->fresh();
        $this->assertSame($result['secret'], $fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_confirmed_at);
        $this->assertFalse($fresh->hasTwoFactorEnabled());
    }

    public function test_execute_resets_recovery_codes_and_confirmation_when_restarting_enrollment(): void
    {
        $user = $this->makeUser([
            'two_factor_secret' => 'OLDSECRET',
            'two_factor_recovery_codes' => ['AAAA-BBBB'],
        ]);

        $result = $this->action->execute($user);

        $fresh = $user->fresh();
        $this->assertNotSame('OLDSECRET', $fresh->two_factor_secret);
        $this->assertSame($result['secret'], $fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_recovery_codes);
    }

    public function test_execute_throws_when_two_factor_is_already_enabled(): void
    {
        $user = $this->makeUser([
            'two_factor_secret' => 'SOMESECRET',
            'two_factor_confirmed_at' => now(),
        ]);

        try {
            $this->action->execute($user);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                'Two-factor authentication is already enabled. Disable it first to re-enroll.',
                $e->errors()['two_factor'][0]
            );
        }

        $this->assertSame('SOMESECRET', $user->fresh()->two_factor_secret);
    }
}
