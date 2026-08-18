<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\SendSupportMessageAction;
use App\Api\V1\Enum\SupportStatusEnum;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class SendSupportMessageActionTest extends TestCase
{
    use RefreshDatabase;

    private SendSupportMessageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(SendSupportMessageAction::class);
    }

    private function makeTicket(User $user, SupportStatusEnum $status = SupportStatusEnum::NEW): SupportTicket
    {
        return SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Help',
            'status' => $status,
            'handled_by' => 'human',
        ]);
    }

    public function test_execute_denies_access_to_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = $this->makeTicket($owner);

        $this->expectException(AccessDeniedHttpException::class);

        $this->action->execute($ticket, $stranger, ['message' => 'Hi'], null);
    }

    public function test_execute_requires_a_message_or_a_file(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Message or file is required');

        $this->action->execute($ticket, $user, ['message' => null], null);
    }

    public function test_execute_creates_the_message(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);

        $message = $this->action->execute($ticket, $user, ['message' => 'Any update?'], null);

        $this->assertSame('Any update?', $message->message);
        $this->assertFalse($message->is_admin);
    }

    public function test_execute_reopens_a_done_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user, SupportStatusEnum::DONE);

        $this->action->execute($ticket, $user, ['message' => 'Still need help'], null);

        $this->assertSame(SupportStatusEnum::ACCEPTED, $ticket->fresh()->status);
    }

    public function test_execute_leaves_a_non_done_ticket_status_unchanged(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user, SupportStatusEnum::ACCEPTED);

        $this->action->execute($ticket, $user, ['message' => 'Follow up'], null);

        $this->assertSame(SupportStatusEnum::ACCEPTED, $ticket->fresh()->status);
    }
}
