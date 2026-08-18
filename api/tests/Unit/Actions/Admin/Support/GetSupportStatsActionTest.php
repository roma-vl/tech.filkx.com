<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\GetSupportStatsAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSupportStatsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetSupportStatsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetSupportStatsAction::class);
    }

    private function makeTicket(array $overrides = []): SupportTicket
    {
        $user = User::factory()->create();

        return SupportTicket::create(array_merge([
            'user_id' => $user->id,
            'subject' => 'Help',
            'status' => 'new',
        ], $overrides));
    }

    public function test_execute_counts_tickets_created_in_the_last_30_days(): void
    {
        $recent = $this->makeTicket();
        $old = $this->makeTicket();
        $old->forceFill(['created_at' => now()->subDays(40)])->save();

        $result = $this->action->execute();

        $this->assertSame(1, $result['totalTickets']);
    }

    public function test_execute_counts_tickets_resolved_in_the_last_30_days(): void
    {
        $this->makeTicket(['status' => 'done']);
        $notDone = $this->makeTicket(['status' => 'new']);

        $result = $this->action->execute();

        $this->assertSame(1, $result['resolvedTickets']);
    }

    public function test_execute_excludes_tickets_resolved_before_the_last_30_days(): void
    {
        $ticket = $this->makeTicket(['status' => 'done']);
        $ticket->forceFill(['updated_at' => now()->subDays(45)])->save();

        $result = $this->action->execute();

        $this->assertSame(0, $result['resolvedTickets']);
    }

    public function test_execute_returns_chart_data_grouped_by_day(): void
    {
        $this->makeTicket();
        $this->makeTicket();

        $result = $this->action->execute();

        $this->assertCount(1, $result['chartData']);
        $this->assertEquals(2, $result['chartData']->first()->total);
    }
}
