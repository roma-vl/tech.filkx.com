<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\MarkSupportTicketAsReadAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkSupportTicketAsReadActionTest extends TestCase
{
    use RefreshDatabase;

    private MarkSupportTicketAsReadAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(MarkSupportTicketAsReadAction::class);
    }

    private function makeTicket(): SupportTicket
    {
        $user = User::factory()->create();

        return SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new']);
    }

    public function test_execute_marks_all_unread_admin_messages_as_read(): void
    {
        $ticket = $this->makeTicket();
        $first = $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Hi', 'is_admin' => false]);
        $second = $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Again', 'is_admin' => false]);

        $this->action->execute($ticket);

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNotNull($second->fresh()->read_at);
    }

    public function test_execute_leaves_admin_messages_untouched(): void
    {
        $ticket = $this->makeTicket();
        $adminMessage = $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Hi', 'is_admin' => true]);

        $this->action->execute($ticket);

        $this->assertNull($adminMessage->fresh()->read_at);
    }

    public function test_execute_does_not_touch_the_ticket_when_there_is_nothing_to_mark(): void
    {
        $ticket = $this->makeTicket();
        $ticket->forceFill(['updated_at' => now()->subDay()])->save();
        $originalUpdatedAt = $ticket->updated_at;

        $this->action->execute($ticket);

        $this->assertEquals($originalUpdatedAt, $ticket->fresh()->updated_at);
    }
}
