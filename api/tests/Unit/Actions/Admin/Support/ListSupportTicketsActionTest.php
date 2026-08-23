<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\ListSupportTicketsAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSupportTicketsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListSupportTicketsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListSupportTicketsAction::class);
    }

    private function makeTicket(array $overrides = []): SupportTicket
    {
        $user = $overrides['user'] ?? User::factory()->create();
        unset($overrides['user']);

        return SupportTicket::create(array_merge([
            'user_id' => $user->id,
            'subject' => 'Help',
            'status' => 'new',
        ], $overrides));
    }

    public function test_execute_excludes_deleted_tickets_by_default(): void
    {
        $this->makeTicket();
        $this->makeTicket(['status' => 'deleted']);

        $result = $this->action->execute();

        $this->assertSame(1, $result->total());
    }

    public function test_execute_filters_by_status(): void
    {
        $this->makeTicket(['status' => 'new']);
        $this->makeTicket(['status' => 'done']);

        $result = $this->action->execute(['status' => 'Done']);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_filters_by_search(): void
    {
        $this->makeTicket(['subject' => 'Broken checkout']);
        $this->makeTicket(['subject' => 'Refund question']);

        $result = $this->action->execute(['search' => 'checkout']);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_filters_by_tag(): void
    {
        $this->makeTicket(['tags' => ['billing']]);
        $this->makeTicket(['tags' => ['shipping']]);

        $result = $this->action->execute(['tag' => 'billing']);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_filters_by_user_id(): void
    {
        $user = User::factory()->create();
        $this->makeTicket(['user' => $user]);
        $this->makeTicket();

        $result = $this->action->execute(['user_id' => $user->id]);

        $this->assertSame(1, $result->total());
    }

    public function test_execute_includes_the_unread_count_for_admin(): void
    {
        $ticket = $this->makeTicket();
        $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Hi', 'is_admin' => false]);
        $ticket->messages()->create(['user_id' => $ticket->user_id, 'message' => 'Read', 'is_admin' => false, 'read_at' => now()]);

        $result = $this->action->execute();

        $this->assertSame(1, $result->items()[0]->unread_count);
    }
}
