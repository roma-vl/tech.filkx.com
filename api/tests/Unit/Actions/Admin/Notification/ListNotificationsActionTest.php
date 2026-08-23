<?php

namespace Tests\Unit\Actions\Admin\Notification;

use App\Api\Admin\Actions\Notification\ListNotificationsAction;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListNotificationsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListNotificationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListNotificationsAction::class);
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

    public function test_execute_returns_all_notifications_ordered_by_newest_first(): void
    {
        $older = $this->makeNotification(['title' => 'Older']);
        $older->created_at = now()->subDay();
        $older->save();
        $newer = $this->makeNotification(['title' => 'Newer']);

        $result = $this->action->execute();

        $this->assertSame(2, $result->total());
        $this->assertSame($newer->id, $result->getCollection()->first()->id);
        $this->assertSame($older->id, $result->getCollection()->last()->id);
    }

    public function test_execute_respects_the_given_per_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makeNotification();
        }

        $result = $this->action->execute(2);

        $this->assertSame(3, $result->total());
        $this->assertCount(2, $result->getCollection());
        $this->assertSame(2, $result->lastPage());
    }

    public function test_execute_returns_empty_paginator_when_no_notifications_exist(): void
    {
        $result = $this->action->execute();

        $this->assertSame(0, $result->total());
    }
}
