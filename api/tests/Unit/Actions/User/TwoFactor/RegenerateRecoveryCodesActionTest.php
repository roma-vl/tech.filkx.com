<?php

namespace Tests\Unit\Actions\User\TwoFactor;

use App\Api\V1\Actions\User\TwoFactor\RegenerateRecoveryCodesAction;
use App\Events\AuditEvent;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class RegenerateRecoveryCodesActionTest extends TestCase
{
    use RefreshDatabase;

    private RegenerateRecoveryCodesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RegenerateRecoveryCodesAction::class);
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

    private function makeTwoFactorEnabledUser(): array
    {
        $secret = app(TwoFactorAuthenticationService::class)->generateSecretKey();
        $oldCodes = ['AAAA-BBBB', 'CCCC-DDDD'];

        $user = $this->makeUser([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $oldCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        return ['user' => $user, 'secret' => $secret, 'oldCodes' => $oldCodes];
    }

    public function test_execute_with_valid_code_returns_ten_new_codes_and_fires_audit_event(): void
    {
        Event::fake([AuditEvent::class]);
        $enrollment = $this->makeTwoFactorEnabledUser();

        $newCodes = $this->action->execute($enrollment['user'], $this->currentCodeFor($enrollment['secret']));

        $this->assertCount(10, $newCodes);
        $this->assertCount(10, array_unique($newCodes));
        $this->assertEmpty(array_intersect($enrollment['oldCodes'], $newCodes));

        $fresh = $enrollment['user']->fresh();
        $this->assertSame($newCodes, $fresh->two_factor_recovery_codes);
        $this->assertTrue($fresh->hasTwoFactorEnabled());

        Event::assertDispatched(
            AuditEvent::class,
            fn ($event) => $event->dto->action === 'auth.2fa_recovery_codes_regenerated'
        );
    }

    public function test_execute_throws_when_two_factor_is_not_enabled(): void
    {
        $user = $this->makeUser();

        try {
            $this->action->execute($user, '000000');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                'Two-factor authentication is not enabled.',
                $e->errors()['two_factor'][0]
            );
        }
    }

    public function test_execute_throws_on_an_invalid_code_and_leaves_old_codes_intact(): void
    {
        $enrollment = $this->makeTwoFactorEnabledUser();

        try {
            $this->action->execute($enrollment['user'], '000000');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame('The provided code is invalid.', $e->errors()['code'][0]);
        }

        $this->assertSame($enrollment['oldCodes'], $enrollment['user']->fresh()->two_factor_recovery_codes);
    }
}
