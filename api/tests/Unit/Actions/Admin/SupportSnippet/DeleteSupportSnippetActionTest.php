<?php

namespace Tests\Unit\Actions\Admin\SupportSnippet;

use App\Api\Admin\Actions\SupportSnippet\DeleteSupportSnippetAction;
use App\Models\SupportSnippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteSupportSnippetActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteSupportSnippetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteSupportSnippetAction::class);
    }

    public function test_execute_deletes_the_snippet(): void
    {
        $snippet = SupportSnippet::create(['title' => 'Greeting', 'content' => 'Hi there!']);

        $this->action->execute($snippet);

        $this->assertDatabaseMissing('support_snippets', ['id' => $snippet->id]);
    }
}
