<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\MarkSupportMessagesReadAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkSupportMessagesReadActionTest extends TestCase
{
    use RefreshDatabase;

    private MarkSupportMessagesReadAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(MarkSupportMessagesReadAction::class);
    }

    private function makeTicket(): SupportTicket
    {
        $user = User::factory()->create();

        return SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new']);
    }

    public function test_execute_marks_the_given_user_messages_as_read(): void
    {
        $ticket = $this->makeTicket();
        $message = $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Hi', 'is_admin' => false]);

        $updated = $this->action->execute($ticket, [$message->id]);

        $this->assertSame(1, $updated);
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_execute_does_not_mark_admin_messages_as_read(): void
    {
        $ticket = $this->makeTicket();
        $adminMessage = $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Hi', 'is_admin' => true]);

        $updated = $this->action->execute($ticket, [$adminMessage->id]);

        $this->assertSame(0, $updated);
        $this->assertNull($adminMessage->fresh()->read_at);
    }

    public function test_execute_returns_zero_for_an_empty_id_list(): void
    {
        $ticket = $this->makeTicket();

        $updated = $this->action->execute($ticket, []);

        $this->assertSame(0, $updated);
    }

    public function test_execute_does_not_touch_the_ticket_when_nothing_was_updated(): void
    {
        $ticket = $this->makeTicket();
        $ticket->forceFill(['updated_at' => now()->subDay()])->save();
        $originalUpdatedAt = $ticket->updated_at;

        $this->action->execute($ticket, []);

        $this->assertEquals($originalUpdatedAt, $ticket->fresh()->updated_at);
    }

    public function test_execute_touches_the_ticket_when_messages_were_updated(): void
    {
        $ticket = $this->makeTicket();
        $ticket->forceFill(['updated_at' => now()->subDay()])->save();
        $message = $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Hi', 'is_admin' => false]);

        $this->action->execute($ticket, [$message->id]);

        $this->assertTrue($ticket->fresh()->updated_at->greaterThan(now()->subMinute()));
    }
}
