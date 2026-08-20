<?php

namespace Tests\Unit\Actions\Notification;

use App\Api\V1\Actions\Notification\MarkNotificationReadAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class MarkNotificationReadActionTest extends TestCase
{
    use RefreshDatabase;

    private MarkNotificationReadAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(MarkNotificationReadAction::class);
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

    public function test_execute_marks_the_users_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user->id);

        $result = $this->action->execute($user, $notification->id);

        $this->assertNotNull($result->read_at);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_execute_marks_a_global_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification(null);

        $result = $this->action->execute($user, $notification->id);

        $this->assertNotNull($result->read_at);
    }

    public function test_execute_throws_not_found_when_the_notification_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Notification not found.');

        $this->action->execute($user, 999999);
    }

    public function test_execute_throws_access_denied_when_the_notification_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = $this->createNotification($otherUser->id);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('This notification does not belong to you.');

        $this->action->execute($user, $notification->id);

        $this->assertNull($notification->fresh()->read_at);
    }
}
