<?php

namespace Tests\Unit\Actions\Admin\AuditLog;

use App\Api\Admin\Actions\AuditLog\ListAuditLogsAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAuditLogsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAuditLogsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAuditLogsAction::class);
    }

    private function makeLog(array $overrides = []): AuditLog
    {
        return AuditLog::create(array_merge([
            'action' => 'settings.updated',
            'domain' => 'admin',
            'message' => 'Something happened',
        ], $overrides));
    }

    public function test_execute_returns_all_logs_ordered_by_newest_first_with_user_loaded(): void
    {
        $user = User::factory()->create();
        $older = $this->makeLog(['created_at' => now()->subDay()]);
        $newer = $this->makeLog(['user_id' => $user->id, 'created_at' => now()]);

        $result = $this->action->execute();

        $this->assertSame(2, $result->total());
        $first = $result->getCollection()->first();
        $this->assertSame($newer->id, $first->id);
        $this->assertSame($older->id, $result->getCollection()->last()->id);
        $this->assertTrue($first->relationLoaded('user'));
        $this->assertSame($user->id, $first->user->id);
    }

    public function test_execute_filters_by_domain(): void
    {
        $match = $this->makeLog(['domain' => 'admin']);
        $this->makeLog(['domain' => 'public']);

        $result = $this->action->execute(['domain' => 'admin']);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->getCollection()->first()->id);
    }

    public function test_execute_filters_by_action(): void
    {
        $match = $this->makeLog(['action' => 'settings.updated']);
        $this->makeLog(['action' => 'role.granted']);

        $result = $this->action->execute(['action' => 'settings.updated']);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->getCollection()->first()->id);
    }

    public function test_execute_filters_by_user_id(): void
    {
        $user = User::factory()->create();
        $match = $this->makeLog(['user_id' => $user->id]);
        $this->makeLog();

        $result = $this->action->execute(['userId' => $user->id]);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->getCollection()->first()->id);
    }

    public function test_execute_filters_by_search_against_message(): void
    {
        $match = $this->makeLog(['message' => 'System settings updated: currency']);
        $this->makeLog(['message' => 'User role changed']);

        $result = $this->action->execute(['search' => 'settings updated']);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->getCollection()->first()->id);
    }

    public function test_execute_respects_per_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makeLog();
        }

        $result = $this->action->execute([], 2);

        $this->assertSame(3, $result->total());
        $this->assertCount(2, $result->getCollection());
        $this->assertSame(2, $result->lastPage());
    }

    public function test_execute_returns_empty_paginator_when_no_logs_exist(): void
    {
        $result = $this->action->execute();

        $this->assertSame(0, $result->total());
    }

    public function test_execute_ignores_empty_filter_values(): void
    {
        $this->makeLog();

        $result = $this->action->execute(['domain' => '', 'action' => '', 'userId' => '', 'search' => '']);

        $this->assertSame(1, $result->total());
    }
}
