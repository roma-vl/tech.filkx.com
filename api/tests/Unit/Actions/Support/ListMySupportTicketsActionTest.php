<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\ListMySupportTicketsAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListMySupportTicketsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListMySupportTicketsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListMySupportTicketsAction::class);
    }

    public function test_execute_only_returns_tickets_belonging_to_the_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Mine', 'status' => 'new', 'handled_by' => 'human']);
        SupportTicket::create(['user_id' => $otherUser->id, 'subject' => 'Not mine', 'status' => 'new', 'handled_by' => 'human']);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame($ticket->id, $result->first()->id);
    }

    public function test_execute_counts_unread_admin_messages_per_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Mine', 'status' => 'new', 'handled_by' => 'human']);
        $ticket->messages()->create(['user_id' => $user->id, 'message' => 'Hi', 'is_admin' => true]);
        $ticket->messages()->create(['user_id' => $user->id, 'message' => 'Still here', 'is_admin' => true]);
        $ticket->messages()->create(['user_id' => $user->id, 'message' => 'My reply', 'is_admin' => false]);

        $result = $this->action->execute($user);

        $this->assertSame(2, $result->first()->unreadCount);
    }
}
