<?php

namespace Tests\Unit\Actions\Admin\Notification;

use App\Api\Admin\Actions\Notification\BroadcastNotificationAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    private BroadcastNotificationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(BroadcastNotificationAction::class);
    }

    public function test_execute_creates_a_notification_for_every_user_when_recipients_is_all(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->action->execute([
            'title' => 'Maintenance',
            'message' => 'The platform will be down tonight.',
            'type' => 'system',
            'recipients' => 'all',
        ]);

        $this->assertSame(2, Notification::count());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userA->id,
            'title' => 'Maintenance',
            'content' => 'The platform will be down tonight.',
            'type' => 'system',
            'link' => null,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $userB->id,
            'title' => 'Maintenance',
        ]);
    }

    public function test_execute_creates_notifications_only_for_the_selected_user_ids(): void
    {
        $selected = User::factory()->create();
        $other = User::factory()->create();

        $this->action->execute([
            'title' => 'Selected offer',
            'message' => 'A special offer for you.',
            'type' => 'promo',
            'recipients' => 'selected',
            'user_ids' => [$selected->id],
            'action_url' => '/promo',
        ]);

        $this->assertSame(1, Notification::count());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $selected->id,
            'title' => 'Selected offer',
            'link' => '/promo',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $other->id,
        ]);
    }

    public function test_execute_creates_nothing_when_no_users_exist_and_recipients_is_all(): void
    {
        $this->action->execute([
            'title' => 'Nobody home',
            'message' => 'No users yet.',
            'type' => 'system',
            'recipients' => 'all',
        ]);

        $this->assertSame(0, Notification::count());
    }
}
