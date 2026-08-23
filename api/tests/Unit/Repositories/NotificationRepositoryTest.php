<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\NotificationRepository;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NotificationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(NotificationRepository::class);
    }

    private function makeNotification(array $overrides = []): Notification
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $notification = Notification::create(array_merge([
            'title' => 'Title',
            'content' => 'Content',
            'type' => 'system',
        ], $overrides));

        if ($createdAt !== null) {
            $notification->created_at = $createdAt;
            $notification->save();
        }

        return $notification;
    }

    // --- paginateAll ---

    public function test_paginate_all_returns_every_notification_ordered_by_created_at_desc(): void
    {
        $older = $this->makeNotification(['created_at' => now()->subDay()]);
        $newer = $this->makeNotification(['created_at' => now()]);

        $result = $this->repository->paginateAll(15);

        $this->assertSame(2, $result->total());
        $this->assertSame($newer->id, $result->getCollection()->first()->id);
        $this->assertSame($older->id, $result->getCollection()->last()->id);
    }

    public function test_paginate_all_returns_empty_when_no_notifications_exist(): void
    {
        $result = $this->repository->paginateAll(15);

        $this->assertSame(0, $result->total());
    }

    public function test_paginate_all_respects_per_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makeNotification();
        }

        $result = $this->repository->paginateAll(2);

        $this->assertSame(3, $result->total());
        $this->assertCount(2, $result->getCollection());
    }

    // --- paginateForUser ---

    public function test_paginate_for_user_returns_the_users_notifications_and_global_ones(): void
    {
        $user = User::factory()->create();
        $mine = $this->makeNotification(['user_id' => $user->id]);
        $global = $this->makeNotification(['user_id' => null]);
        $other = User::factory()->create();
        $this->makeNotification(['user_id' => $other->id]);

        $result = $this->repository->paginateForUser($user->id, 15);

        $this->assertSame(2, $result->total());
        $ids = $result->getCollection()->pluck('id')->all();
        $this->assertContains($mine->id, $ids);
        $this->assertContains($global->id, $ids);
    }

    public function test_paginate_for_user_with_null_user_id_returns_only_global_notifications(): void
    {
        $user = User::factory()->create();
        $this->makeNotification(['user_id' => $user->id]);
        $global = $this->makeNotification(['user_id' => null]);

        $result = $this->repository->paginateForUser(null, 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($global->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_for_user_returns_empty_when_user_has_no_notifications_and_none_global(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->makeNotification(['user_id' => $other->id]);

        $result = $this->repository->paginateForUser($user->id, 15);

        $this->assertSame(0, $result->total());
    }

    // --- create ---

    public function test_create_persists_a_new_notification(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->create([
            'user_id' => $user->id,
            'title' => 'New',
            'content' => 'Body',
            'type' => 'order',
        ]);

        $this->assertNotNull($result->id);
        $this->assertDatabaseHas('notifications', [
            'id' => $result->id,
            'title' => 'New',
            'type' => 'order',
        ]);
    }

    // --- markAsRead ---

    public function test_mark_as_read_sets_read_at_and_returns_the_notification(): void
    {
        $notification = $this->makeNotification(['read_at' => null]);

        $result = $this->repository->markAsRead($notification);

        $this->assertNotNull($result->read_at);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    // --- markAllAsRead ---

    public function test_mark_all_as_read_marks_only_the_given_users_unread_notifications(): void
    {
        $user = User::factory()->create();
        $unread = $this->makeNotification(['user_id' => $user->id, 'read_at' => null]);
        $alreadyRead = $this->makeNotification(['user_id' => $user->id, 'read_at' => now()->subDay()]);
        $other = User::factory()->create();
        $othersUnread = $this->makeNotification(['user_id' => $other->id, 'read_at' => null]);

        $this->repository->markAllAsRead($user->id);

        $this->assertNotNull($unread->fresh()->read_at);
        $this->assertNotNull($alreadyRead->fresh()->read_at);
        $this->assertNull($othersUnread->fresh()->read_at);
    }

    public function test_mark_all_as_read_does_nothing_when_user_has_no_unread_notifications(): void
    {
        $user = User::factory()->create();

        $this->repository->markAllAsRead($user->id);

        $this->assertDatabaseCount('notifications', 0);
    }

    // --- find ---

    public function test_find_returns_the_notification(): void
    {
        $notification = $this->makeNotification();

        $result = $this->repository->find($notification->id);

        $this->assertNotNull($result);
        $this->assertSame($notification->id, $result->id);
    }

    public function test_find_returns_null_when_notification_does_not_exist(): void
    {
        $result = $this->repository->find(999999);

        $this->assertNull($result);
    }

    // --- delete ---

    public function test_delete_removes_the_notification_and_returns_true(): void
    {
        $notification = $this->makeNotification();

        $result = $this->repository->delete($notification);

        $this->assertTrue($result);
        $this->assertNull(Notification::find($notification->id));
    }
}
