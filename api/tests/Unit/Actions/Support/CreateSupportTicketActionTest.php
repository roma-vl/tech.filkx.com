<?php

namespace Tests\Unit\Actions\Support;

use App\Api\V1\Actions\Support\CreateSupportTicketAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateSupportTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateSupportTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateSupportTicketAction::class);
    }

    public function test_execute_creates_a_new_ticket_with_its_first_message(): void
    {
        $user = User::factory()->create();

        $ticket = $this->action->execute($user, [
            'subject' => 'Where is my order?',
            'message' => 'It has been a week.',
        ], null);

        $this->assertSame('Where is my order?', $ticket->subject);
        $this->assertSame('new', $ticket->status->value);
        $this->assertSame('human', $ticket->handled_by);
        $this->assertCount(1, $ticket->messages);
        $this->assertSame('It has been a week.', $ticket->messages->first()->message);
        $this->assertFalse($ticket->messages->first()->is_admin);
    }

    public function test_execute_attaches_an_uploaded_file_to_the_first_message(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('receipt.pdf', 50, 'application/pdf');

        $ticket = $this->action->execute($user, [
            'subject' => 'Refund request',
            'message' => 'Attached is my receipt.',
        ], $file);

        $message = $ticket->messages->first();
        Storage::disk('public')->assertExists($message->file_path);
        $this->assertSame('receipt.pdf', $message->file_name);
    }

    public function test_execute_links_the_ticket_to_a_product_when_given(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $ticket = $this->action->execute($user, [
            'subject' => 'Чи є чохол для цього телефону?',
            'message' => 'Питаю саме про цей товар.',
            'product_id' => $product->id,
        ], null);

        $this->assertSame($product->id, $ticket->product_id);
        $this->assertTrue($ticket->relationLoaded('product'));
        $this->assertSame($product->id, $ticket->product->id);
    }

    public function test_execute_leaves_the_ticket_unlinked_when_no_product_is_given(): void
    {
        $user = User::factory()->create();

        $ticket = $this->action->execute($user, [
            'subject' => 'General question',
            'message' => 'Not about a specific product.',
        ], null);

        $this->assertNull($ticket->product_id);
        $this->assertNull($ticket->product);
    }
}
