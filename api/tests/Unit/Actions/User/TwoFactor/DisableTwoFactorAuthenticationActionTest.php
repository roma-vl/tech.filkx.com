<?php

namespace Tests\Unit\Actions\User\TwoFactor;

use App\Api\V1\Actions\User\TwoFactor\DisableTwoFactorAuthenticationAction;
use App\Events\AuditEvent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DisableTwoFactorAuthenticationActionTest extends TestCase
{
    use RefreshDatabase;

    private DisableTwoFactorAuthenticationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DisableTwoFactorAuthenticationAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    private function makeTwoFactorEnabledUser(string $password = 'secret123'): User
    {
        return $this->makeUser([
            'password' => Hash::make($password),
            'two_factor_secret' => 'SOMESECRET',
            'two_factor_recovery_codes' => ['AAAA-BBBB'],
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_execute_with_correct_password_disables_two_factor_and_fires_audit_event(): void
    {
        Event::fake([AuditEvent::class]);
        $user = $this->makeTwoFactorEnabledUser('secret123');

        $this->action->execute($user, 'secret123');

        $fresh = $user->fresh();
        $this->assertFalse($fresh->hasTwoFactorEnabled());
        $this->assertNull($fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_recovery_codes);
        $this->assertNull($fresh->two_factor_confirmed_at);

        Event::assertDispatched(AuditEvent::class, fn ($event) => $event->dto->action === 'auth.2fa_disabled');
    }

    public function test_execute_throws_on_incorrect_password_and_leaves_two_factor_enabled(): void
    {
        $user = $this->makeTwoFactorEnabledUser('secret123');

        try {
            $this->action->execute($user, 'wrong-password');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame('The provided password is incorrect.', $e->errors()['password'][0]);
        }

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }
}
