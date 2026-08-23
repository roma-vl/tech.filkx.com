<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\UpdateSupportTicketStatusAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSupportTicketStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateSupportTicketStatusAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateSupportTicketStatusAction::class);
    }

    private function makeTicket(array $overrides = []): SupportTicket
    {
        $user = User::factory()->create();

        return SupportTicket::create(array_merge([
            'user_id' => $user->id,
            'subject' => 'Help',
            'status' => 'new',
            'handled_by' => 'human',
        ], $overrides));
    }

    public function test_execute_updates_the_status(): void
    {
        $ticket = $this->makeTicket();

        $result = $this->action->execute($ticket, 'done');

        $this->assertSame('done', $result->status->value);
    }

    public function test_execute_switches_handled_by_to_human_when_ai_was_handling_it(): void
    {
        $ticket = $this->makeTicket(['handled_by' => 'ai']);

        $result = $this->action->execute($ticket, 'accepted');

        $this->assertSame('human', $result->handled_by);
    }

    public function test_execute_leaves_handled_by_untouched_when_already_human(): void
    {
        $ticket = $this->makeTicket(['handled_by' => 'human']);

        $result = $this->action->execute($ticket, 'accepted');

        $this->assertSame('human', $result->handled_by);
    }
}
