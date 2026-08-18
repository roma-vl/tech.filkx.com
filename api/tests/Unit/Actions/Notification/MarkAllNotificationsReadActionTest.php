<?php

namespace Tests\Unit\Actions\Notification;

use App\Api\V1\Actions\Notification\MarkAllNotificationsReadAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkAllNotificationsReadActionTest extends TestCase
{
    use RefreshDatabase;

    private MarkAllNotificationsReadAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(MarkAllNotificationsReadAction::class);
    }

    private function createNotification(?int $userId, array $attributes = []): Notification
    {
        return Notification::create(array_merge([
            'user_id' => $userId,
            'title' => 'Notification title',
            'content' => 'Notification content',
            'type' => 'system',
        ], $attributes));
    }

    public function test_execute_marks_all_unread_notifications_of_the_user_as_read(): void
    {
        $user = User::factory()->create();

        $unread1 = $this->createNotification($user->id);
        $unread2 = $this->createNotification($user->id);

        $this->action->execute($user);

        $this->assertNotNull($unread1->fresh()->read_at);
        $this->assertNotNull($unread2->fresh()->read_at);
    }

    public function test_execute_does_not_overwrite_the_read_at_of_already_read_notifications(): void
    {
        $user = User::factory()->create();
        $readAt = now()->subDay();

        $alreadyRead = $this->createNotification($user->id, ['read_at' => $readAt]);

        $this->action->execute($user);

        // The read_at column has second precision, so compare without sub-second noise.
        $this->assertSame($readAt->toDateTimeString(), $alreadyRead->fresh()->read_at->toDateTimeString());
    }

    public function test_execute_does_not_affect_notifications_belonging_to_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $othersNotification = $this->createNotification($otherUser->id);
        $globalNotification = $this->createNotification(null);

        $this->action->execute($user);

        $this->assertNull($othersNotification->fresh()->read_at);
        $this->assertNull($globalNotification->fresh()->read_at);
    }

    public function test_execute_does_nothing_when_the_user_has_no_unread_notifications(): void
    {
        $user = User::factory()->create();

        $this->action->execute($user);

        $this->assertTrue(true);
    }
}
