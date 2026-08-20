<?php

namespace Tests\Unit\Actions\Admin\Support;

use App\Api\Admin\Actions\Support\ReplySupportTicketAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReplySupportTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private ReplySupportTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ReplySupportTicketAction::class);
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

    public function test_execute_creates_an_admin_message(): void
    {
        $admin = User::factory()->create();
        Auth::login($admin);
        $ticket = $this->makeTicket();

        $message = $this->action->execute($ticket, ['message' => 'We are looking into it']);

        $this->assertSame('We are looking into it', $message->message);
        $this->assertTrue((bool) $message->is_admin);
        $this->assertSame($admin->id, $message->user_id);
    }

    public function test_execute_stores_an_attached_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        Auth::login($admin);
        $ticket = $this->makeTicket();
        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $message = $this->action->execute($ticket, ['message' => 'See attached'], $file);

        $this->assertNotNull($message->file_path);
        Storage::disk('public')->assertExists($message->file_path);
        $this->assertSame('receipt.pdf', $message->file_name);
    }

    public function test_execute_accepts_an_internal_note_without_changing_ticket_status(): void
    {
        $admin = User::factory()->create();
        Auth::login($admin);
        $ticket = $this->makeTicket(['status' => 'new']);

        $message = $this->action->execute($ticket, ['message' => 'Internal note', 'is_internal' => true]);

        $this->assertTrue((bool) $message->is_internal);
        $this->assertSame('new', $ticket->fresh()->status->value);
    }

    public function test_execute_moves_a_new_ticket_to_accepted_on_a_public_reply(): void
    {
        $admin = User::factory()->create();
        Auth::login($admin);
        $ticket = $this->makeTicket(['status' => 'new', 'handled_by' => 'human']);

        $this->action->execute($ticket, ['message' => 'Reply']);

        $ticket->refresh();
        $this->assertSame('accepted', $ticket->status->value);
    }

    public function test_execute_switches_handled_by_to_human_when_ai_was_handling_it(): void
    {
        $admin = User::factory()->create();
        Auth::login($admin);
        $ticket = $this->makeTicket(['status' => 'accepted', 'handled_by' => 'ai']);

        $this->action->execute($ticket, ['message' => 'Reply']);

        $ticket->refresh();
        $this->assertSame('human', $ticket->handled_by);
        $this->assertSame('accepted', $ticket->status->value);
    }

    public function test_execute_does_not_touch_status_when_already_accepted_and_human_handled(): void
    {
        $admin = User::factory()->create();
        Auth::login($admin);
        $ticket = $this->makeTicket(['status' => 'done', 'handled_by' => 'human']);

        $this->action->execute($ticket, ['message' => 'Reply']);

        $ticket->refresh();
        $this->assertSame('done', $ticket->status->value);
    }
}
