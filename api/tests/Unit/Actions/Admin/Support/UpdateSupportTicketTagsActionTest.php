<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\UpdateSupportTicketTagsAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSupportTicketTagsActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateSupportTicketTagsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateSupportTicketTagsAction::class);
    }

    public function test_execute_updates_the_tags(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new', 'tags' => ['billing']]);

        $result = $this->action->execute($ticket, ['billing', 'urgent']);

        $this->assertSame(['billing', 'urgent'], $result->tags);
        $this->assertSame(['billing', 'urgent'], $ticket->fresh()->tags);
    }

    public function test_execute_can_clear_the_tags(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::create(['user_id' => $user->id, 'subject' => 'Help', 'status' => 'new', 'tags' => ['billing']]);

        $this->action->execute($ticket, []);

        $this->assertSame([], $ticket->fresh()->tags);
    }
}
