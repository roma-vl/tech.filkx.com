<?php

namespace Tests\Unit\Actions\Admin\SupportSnippet;

use App\Api\Admin\Actions\SupportSnippet\CreateSupportSnippetAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSupportSnippetActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateSupportSnippetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateSupportSnippetAction::class);
    }

    public function test_execute_creates_the_snippet(): void
    {
        $snippet = $this->action->execute(['title' => 'Greeting', 'content' => 'Hi there!']);

        $this->assertDatabaseHas('support_snippets', [
            'id' => $snippet->id,
            'title' => 'Greeting',
            'content' => 'Hi there!',
        ]);
    }
}
