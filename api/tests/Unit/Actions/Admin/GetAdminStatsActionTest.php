<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\GetAdminStatsAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAdminStatsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetAdminStatsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetAdminStatsAction::class);
    }

    public function test_execute_returns_the_expected_shape(): void
    {
        $result = $this->action->execute();

        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('recent_users', $result);
        $this->assertArrayHasKey('unread_tickets', $result);
        $this->assertCount(4, $result['stats']);
    }

    public function test_execute_reports_the_total_user_count(): void
    {
        User::factory()->count(3)->create();

        $result = $this->action->execute();

        $this->assertSame('3', $result['stats'][0]['value']);
    }

    public function test_execute_returns_at_most_the_five_most_recent_users(): void
    {
        User::factory()->count(7)->create();

        $result = $this->action->execute();

        $this->assertCount(5, $result['recent_users']);
    }

    public function test_execute_reports_the_ticket_count(): void
    {
        $user = User::factory()->create();
        SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new']);
        SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help 2', 'status' => 'done']);

        $result = $this->action->execute();

        $this->assertSame(2, $result['unread_tickets']);
    }
}
