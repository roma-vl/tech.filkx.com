<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\MarkSupportTicketAsReadAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    public function test_execute_marks_the_ticket_and_its_admin_messages_as_read(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new', 'handled_by' => 'human', 'read_at' => null]);
        $adminMessage = $ticket->messages()->create(['user_id' => $user->id, 'message' => 'Reply', 'is_admin' => true]);
        $ownMessage = $ticket->messages()->create(['user_id' => $user->id, 'message' => 'Mine', 'is_admin' => false]);

        $this->action->execute($ticket, $user);

        $this->assertNotNull($ticket->fresh()->read_at);
        $this->assertNotNull($adminMessage->fresh()->read_at);
        $this->assertNull($ownMessage->fresh()->read_at);
    }

    public function test_execute_aborts_with_403_for_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $owner->id, 'subject' => 'Help', 'status' => 'new', 'handled_by' => 'human']);

        try {
            $this->action->execute($ticket, $stranger);
            $this->fail('Expected an HttpException to be thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
