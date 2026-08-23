<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\ListSupportTicketMessagesAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSupportTicketMessagesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListSupportTicketMessagesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListSupportTicketMessagesAction::class);
    }

    private function makeTicketWithMessages(int $count): SupportTicket
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new']);

        for ($i = 1; $i <= $count; $i++) {
            $message = $ticket->messages()->create([
                'user_id' => $user->id,
                'message' => "Message {$i}",
                'is_admin' => false,
            ]);
            $message->forceFill(['created_at' => now()->addSeconds($i)])->save();
        }

        return $ticket;
    }

    public function test_execute_returns_the_most_recent_messages_oldest_first(): void
    {
        $ticket = $this->makeTicketWithMessages(3);

        $result = $this->action->execute($ticket, null, 5);

        $this->assertCount(3, $result);
        $this->assertSame('Message 1', $result->first()->message);
        $this->assertSame('Message 3', $result->last()->message);
    }

    public function test_execute_respects_the_limit(): void
    {
        $ticket = $this->makeTicketWithMessages(5);

        $result = $this->action->execute($ticket, null, 2);

        $this->assertCount(2, $result);
        $this->assertSame('Message 4', $result->first()->message);
        $this->assertSame('Message 5', $result->last()->message);
    }

    public function test_execute_loads_messages_before_a_given_id(): void
    {
        $ticket = $this->makeTicketWithMessages(5);
        $middleId = $ticket->messages()->orderBy('id')->skip(2)->first()->id;

        $result = $this->action->execute($ticket, $middleId, 5);

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn ($m) => $m->id < $middleId));
    }

    public function test_execute_returns_an_empty_collection_when_no_messages_exist(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new']);

        $result = $this->action->execute($ticket);

        $this->assertCount(0, $result);
    }
}
