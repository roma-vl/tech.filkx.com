<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\ConfirmEmailChangeAction;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ConfirmEmailChangeActionTest extends TestCase
{
    use RefreshDatabase;

    private ConfirmEmailChangeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ConfirmEmailChangeAction::class);
    }

    public function test_execute_updates_the_email_and_resets_verification(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $result = $this->action->execute($user->id, 'new@example.com');

        $this->assertTrue($result);
        $fresh = $user->fresh();
        $this->assertSame('new@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
    }

    public function test_execute_sends_a_verification_notification_to_the_new_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->action->execute($user->id, 'new@example.com');

        Notification::assertSentTo($user->fresh(), VerifyEmailNotification::class);
    }

    public function test_execute_returns_false_and_leaves_the_email_unchanged_when_new_email_is_already_taken(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'old@example.com']);

        $result = $this->action->execute($user->id, 'taken@example.com');

        $this->assertFalse($result);
        $this->assertSame('old@example.com', $user->fresh()->email);
        Notification::assertNothingSent();
    }

    public function test_execute_throws_when_user_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, 'new@example.com');
    }
}
