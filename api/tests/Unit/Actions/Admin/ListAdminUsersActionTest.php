<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\ListAdminUsersAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminUsersActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminUsersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminUsersAction::class);
    }

    public function test_execute_returns_a_paginated_list_of_users(): void
    {
        User::factory()->count(3)->create();

        $result = $this->action->execute();

        $this->assertSame(3, $result->total());
    }

    public function test_execute_respects_the_per_page_argument(): void
    {
        User::factory()->count(5)->create();

        $result = $this->action->execute([], 2);

        $this->assertCount(2, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_execute_filters_by_search_on_name(): void
    {
        User::factory()->create(['name' => 'Findable User', 'email' => 'x@example.com']);
        User::factory()->create(['name' => 'Other User', 'email' => 'y@example.com']);

        $result = $this->action->execute(['search' => 'findable']);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_filters_by_search_on_email(): void
    {
        User::factory()->create(['name' => 'A', 'email' => 'special@example.com']);
        User::factory()->create(['name' => 'B', 'email' => 'other@example.com']);

        $result = $this->action->execute(['search' => 'special']);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_filters_by_search_on_id(): void
    {
        $match = User::factory()->create();
        User::factory()->create();

        $result = $this->action->execute(['search' => (string) $match->id]);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->items()[0]->id);
    }

    public function test_execute_filters_by_status(): void
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'suspended']);

        $result = $this->action->execute(['status' => 'suspended']);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_status_deleted_only_returns_soft_deleted_users(): void
    {
        $active = User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        $result = $this->action->execute(['status' => 'deleted']);

        $this->assertSame(1, $result->total());
        $this->assertSame($deleted->id, $result->items()[0]->id);
    }

    public function test_execute_with_deleted_includes_soft_deleted_users_alongside_active_ones(): void
    {
        User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        $result = $this->action->execute(['with_deleted' => true]);

        $this->assertSame(2, $result->total());
    }

    public function test_execute_filters_by_date_range(): void
    {
        $inRange = User::factory()->create();
        $inRange->forceFill(['created_at' => '2026-01-15 10:00:00'])->save();

        $outOfRange = User::factory()->create();
        $outOfRange->forceFill(['created_at' => '2026-03-01 10:00:00'])->save();

        $result = $this->action->execute(['date_from' => '2026-01-01', 'date_to' => '2026-01-31']);

        $this->assertSame(1, $result->total());
        $this->assertSame($inRange->id, $result->items()[0]->id);
    }
}
