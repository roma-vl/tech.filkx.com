<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\UpdateUserPasswordAction;
use App\Models\Role;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateUserPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserPasswordAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateUserPasswordAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_updates_the_password_when_current_password_is_correct(): void
    {
        $user = $this->makeUser(['password' => Hash::make('old-password')]);

        $result = $this->action->execute($user, 'old-password', 'new-password');

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_execute_returns_false_and_leaves_password_unchanged_when_current_password_is_wrong(): void
    {
        $user = $this->makeUser(['password' => Hash::make('old-password')]);

        $result = $this->action->execute($user, 'wrong-password', 'new-password');

        $this->assertFalse($result);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_execute_sends_a_password_changed_notification_on_success(): void
    {
        Notification::fake();
        $user = $this->makeUser(['password' => Hash::make('old-password')]);

        $this->action->execute($user, 'old-password', 'new-password');

        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_execute_does_not_send_a_notification_when_the_current_password_is_wrong(): void
    {
        Notification::fake();
        $user = $this->makeUser(['password' => Hash::make('old-password')]);

        $this->action->execute($user, 'wrong-password', 'new-password');

        Notification::assertNothingSent();
    }
}
