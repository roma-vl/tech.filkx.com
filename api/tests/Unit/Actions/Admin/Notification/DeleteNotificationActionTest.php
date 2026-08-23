<?php

namespace Tests\Unit\Actions\Admin\Notification;

use App\Api\Admin\Actions\Notification\DeleteNotificationAction;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class DeleteNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteNotificationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteNotificationAction::class);
    }

    private function makeNotification(array $overrides = []): Notification
    {
        return Notification::create(array_merge([
            'user_id' => null,
            'title' => 'Title',
            'content' => 'Content',
            'type' => 'system',
        ], $overrides));
    }

    public function test_execute_deletes_the_notification(): void
    {
        $notification = $this->makeNotification();

        $this->action->execute($notification->id);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_execute_throws_not_found_for_an_unknown_notification(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Notification not found.');

        $this->action->execute(999999);
    }
}
