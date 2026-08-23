<?php

namespace Tests\Unit\Actions\Notification;

use App\Api\V1\Actions\Notification\GetUserNotificationsAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserNotificationsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserNotificationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserNotificationsAction::class);
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

    public function test_execute_returns_empty_paginator_when_user_has_no_notifications(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user);

        $this->assertSame(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    public function test_execute_returns_notifications_belonging_to_the_user_and_global_ones(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownNotification = $this->createNotification($user->id, ['title' => 'Own']);
        $globalNotification = $this->createNotification(null, ['title' => 'Global']);
        $this->createNotification($otherUser->id, ['title' => 'Other users']);

        $result = $this->action->execute($user);

        $this->assertSame(2, $result->total());
        $ids = collect($result->items())->pluck('id')->all();
        $this->assertContains($ownNotification->id, $ids);
        $this->assertContains($globalNotification->id, $ids);
    }

    public function test_execute_orders_notifications_by_most_recently_created_first(): void
    {
        $user = User::factory()->create();

        // created_at is not mass-assignable, and Eloquent stamps it on insert regardless,
        // so backdate it with a direct save() (which only touches updated_at on an existing model).
        $older = $this->createNotification($user->id, ['title' => 'Older']);
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->createNotification($user->id, ['title' => 'Newer']);

        $result = $this->action->execute($user);

        $ids = collect($result->items())->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_execute_paginates_using_the_given_per_page_value(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->createNotification($user->id, ['title' => "Notification {$i}"]);
        }

        $result = $this->action->execute($user, 2);

        $this->assertSame(3, $result->total());
        $this->assertSame(2, $result->perPage());
        $this->assertCount(2, $result->items());
        $this->assertSame(2, $result->lastPage());
    }
}
