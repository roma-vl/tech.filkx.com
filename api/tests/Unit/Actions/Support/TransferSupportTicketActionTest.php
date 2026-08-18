<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\TransferSupportTicketAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class TransferSupportTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private TransferSupportTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(TransferSupportTicketAction::class);
    }

    public function test_execute_updates_handled_by_for_the_owner(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new', 'handled_by' => 'ai']);

        $result = $this->action->execute($ticket, $user, 'human');

        $this->assertSame('human', $result->handled_by);
        $this->assertSame('human', $ticket->fresh()->handled_by);
    }

    public function test_execute_denies_access_to_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $owner->id, 'subject' => 'Help', 'status' => 'new', 'handled_by' => 'human']);

        $this->expectException(AccessDeniedHttpException::class);

        $this->action->execute($ticket, $stranger, 'ai');
    }
}
