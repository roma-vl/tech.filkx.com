<?php

namespace Tests\Feature\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function makeTicket(User $user, array $overrides = []): SupportTicket
    {
        return SupportTicket::create(array_merge([
            'user_id' => $user->id,
            'subject' => 'Help',
            'status' => 'new',
            'handled_by' => 'human',
        ], $overrides));
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/support/tickets')->assertStatus(401);
    }

    public function test_index_only_returns_the_authenticated_users_tickets(): void
    {
        $user = User::factory()->create();
        $this->makeTicket($user, ['subject' => 'Mine']);
        $this->makeTicket(User::factory()->create(), ['subject' => 'Not mine']);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/support/tickets');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_store_creates_a_ticket_with_its_first_message(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/support/tickets', [
                'subject' => 'Where is my order?',
                'message' => 'It has been a week.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.subject', 'Where is my order?')
            ->assertJsonCount(1, 'data.messages');
        $this->assertDatabaseHas('support_tickets', ['user_id' => $user->id, 'subject' => 'Where is my order?']);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))->postJson('/api/support/tickets', []);

        $response->assertStatus(422);
    }

    public function test_show_returns_the_ticket_for_its_owner(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);

        $response = $this->withHeaders($this->authHeader($user))->getJson("/api/support/tickets/{$ticket->id}");

        $response->assertOk()->assertJsonPath('data.id', $ticket->id);
    }

    public function test_show_denies_access_to_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = $this->makeTicket($owner);

        $response = $this->withHeaders($this->authHeader($stranger))->getJson("/api/support/tickets/{$ticket->id}");

        $response->assertStatus(403)->assertJsonPath('status', 'error');
    }

    public function test_mark_as_read_marks_admin_messages_as_read(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);
        $ticket->messages()->create(['user_id' => $user->id, 'message' => 'Reply', 'is_admin' => true]);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/support/tickets/{$ticket->id}/mark-as-read");

        $response->assertStatus(204);
        $this->assertNotNull($ticket->fresh()->read_at);
    }

    public function test_mark_as_read_denies_access_to_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = $this->makeTicket($owner);

        $response = $this->withHeaders($this->authHeader($stranger))
            ->postJson("/api/support/tickets/{$ticket->id}/mark-as-read");

        $response->assertStatus(403);
    }

    public function test_send_message_creates_a_message_on_the_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/support/tickets/{$ticket->id}/message", ['message' => 'Any update?']);

        $response->assertOk()->assertJsonPath('data.message', 'Any update?');
    }

    public function test_send_message_requires_a_message_or_a_file(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/support/tickets/{$ticket->id}/message", []);

        $response->assertStatus(422)->assertJsonPath('message', 'Message or file is required');
    }

    public function test_send_message_accepts_a_file_attachment(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user);

        $response = $this->withHeaders($this->authHeader($user))
            ->post("/api/support/tickets/{$ticket->id}/message", [
                'file' => UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf'),
            ]);

        $response->assertOk()->assertJsonPath('data.fileName', 'receipt.pdf');
    }

    public function test_send_message_denies_access_to_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = $this->makeTicket($owner);

        $response = $this->withHeaders($this->authHeader($stranger))
            ->postJson("/api/support/tickets/{$ticket->id}/message", ['message' => 'Hi']);

        $response->assertStatus(403);
    }

    public function test_transfer_hands_the_ticket_to_a_human(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user, ['handled_by' => 'ai']);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/support/tickets/{$ticket->id}/transfer");

        $response->assertOk()->assertJsonPath('data.handledBy', 'human');
    }

    public function test_transfer_to_ai_hands_the_ticket_to_the_assistant(): void
    {
        $user = User::factory()->create();
        $ticket = $this->makeTicket($user, ['handled_by' => 'human']);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/support/tickets/{$ticket->id}/transfer-to-ai");

        $response->assertOk()->assertJsonPath('data.handledBy', 'ai');
    }
}
