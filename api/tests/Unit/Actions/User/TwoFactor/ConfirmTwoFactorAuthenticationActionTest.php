<?php

namespace Tests\Unit\Actions\User\TwoFactor;

use App\Api\V1\Actions\User\TwoFactor\ConfirmTwoFactorAuthenticationAction;
use App\Events\AuditEvent;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ConfirmTwoFactorAuthenticationActionTest extends TestCase
{
    use RefreshDatabase;

    private ConfirmTwoFactorAuthenticationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ConfirmTwoFactorAuthenticationAction::class);
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

    private function generateSecret(): string
    {
        return app(TwoFactorAuthenticationService::class)->generateSecretKey();
    }

    public function test_execute_with_correct_code_enables_two_factor_and_returns_ten_recovery_codes(): void
    {
        Event::fake([AuditEvent::class]);
        $secret = $this->generateSecret();
        $user = $this->makeUser(['two_factor_secret' => $secret]);

        $recoveryCodes = $this->action->execute($user, $this->currentCodeFor($secret));

        $this->assertCount(10, $recoveryCodes);
        $this->assertCount(10, array_unique($recoveryCodes));

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasTwoFactorEnabled());
        $this->assertSame($recoveryCodes, $fresh->two_factor_recovery_codes);

        Event::assertDispatched(AuditEvent::class, fn ($event) => $event->dto->action === 'auth.2fa_enabled');
    }

    public function test_execute_throws_when_there_is_no_pending_enrollment(): void
    {
        $user = $this->makeUser(); // no two_factor_secret at all

        try {
            $this->action->execute($user, '000000');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                'No pending two-factor enrollment to confirm.',
                $e->errors()['two_factor'][0]
            );
        }
    }

    public function test_execute_throws_when_two_factor_is_already_confirmed(): void
    {
        $secret = $this->generateSecret();
        $user = $this->makeUser([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        $this->action->execute($user, $this->currentCodeFor($secret));
    }

    public function test_execute_throws_on_an_invalid_code_and_does_not_enable_two_factor(): void
    {
        $secret = $this->generateSecret();
        $user = $this->makeUser(['two_factor_secret' => $secret]);

        try {
            $this->action->execute($user, '000000');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame('The provided code is invalid.', $e->errors()['code'][0]);
        }

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }
}
