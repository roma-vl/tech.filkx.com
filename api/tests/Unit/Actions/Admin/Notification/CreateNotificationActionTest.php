<?php

namespace Tests\Unit\Actions\Admin\Notification;

use App\Api\Admin\Actions\Notification\CreateNotificationAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateNotificationAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateNotificationAction::class);
    }

    public function test_execute_creates_and_returns_the_notification(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute([
            'user_id' => $user->id,
            'title' => 'Order shipped',
            'content' => 'Your order has been shipped.',
            'type' => 'order',
            'link' => '/orders/1',
        ]);

        $this->assertInstanceOf(Notification::class, $result);
        $this->assertTrue($result->exists);
        $this->assertSame($user->id, $result->user_id);
        $this->assertSame('Order shipped', $result->title);
        $this->assertDatabaseHas('notifications', [
            'id' => $result->id,
            'user_id' => $user->id,
            'title' => 'Order shipped',
        ]);
    }

    public function test_execute_creates_a_global_notification_when_user_id_is_null(): void
    {
        $result = $this->action->execute([
            'user_id' => null,
            'title' => 'Platform maintenance',
            'content' => 'The platform will be down for maintenance.',
            'type' => 'system',
        ]);

        $this->assertNull($result->user_id);
        $this->assertDatabaseHas('notifications', [
            'id' => $result->id,
            'user_id' => null,
        ]);
    }
}
