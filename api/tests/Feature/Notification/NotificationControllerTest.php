<?php

namespace Tests\Feature\Notification;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
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

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertUnauthorized();
    }

    public function test_index_returns_the_users_notifications_in_the_documented_shape(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user->id, ['title' => 'Order shipped', 'link' => '/orders/1']);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $notification->id)
            ->assertJsonPath('data.data.0.userId', $user->id)
            ->assertJsonPath('data.data.0.title', 'Order shipped')
            ->assertJsonPath('data.data.0.link', '/orders/1')
            ->assertJsonPath('data.data.0.readAt', null);
    }

    public function test_mark_read_requires_authentication(): void
    {
        $notification = $this->createNotification(null);

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertUnauthorized();
    }

    public function test_mark_read_marks_the_users_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotification($user->id);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('data.id', $notification->id);
        $this->assertNotNull($response->json('data.readAt'));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_read_returns_404_for_an_unknown_notification(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/notifications/999999/read');

        $response->assertNotFound();
    }

    public function test_mark_read_returns_403_when_the_notification_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = $this->createNotification($otherUser->id);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_read_requires_authentication(): void
    {
        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertUnauthorized();
    }

    public function test_mark_all_read_marks_every_unread_notification_of_the_user_as_read(): void
    {
        $user = User::factory()->create();
        $first = $this->createNotification($user->id);
        $second = $this->createNotification($user->id);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/notifications/mark-all-read');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'All notifications marked as read');
        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNotNull($second->fresh()->read_at);
    }
}
