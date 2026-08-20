<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\GetSupportTicketAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class GetSupportTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private GetSupportTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetSupportTicketAction::class);
    }

    public function test_execute_returns_the_ticket_for_its_owner(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new', 'handled_by' => 'human']);

        $result = $this->action->execute($ticket, $user);

        $this->assertSame($ticket->id, $result->id);
    }

    public function test_execute_denies_access_to_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $owner->id, 'subject' => 'Help', 'status' => 'new', 'handled_by' => 'human']);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Access denied');

        $this->action->execute($ticket, $stranger);
    }
}
