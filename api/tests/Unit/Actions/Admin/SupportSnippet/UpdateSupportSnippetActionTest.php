<?php

namespace Tests\Unit\Actions\Admin\SupportSnippet;

use App\Api\Admin\Actions\SupportSnippet\UpdateSupportSnippetAction;
use App\Models\SupportSnippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSupportSnippetActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateSupportSnippetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateSupportSnippetAction::class);
    }

    public function test_execute_updates_and_returns_the_snippet(): void
    {
        $snippet = SupportSnippet::create(['title' => 'Old', 'content' => 'Old content']);

        $updated = $this->action->execute($snippet, ['title' => 'New', 'content' => 'New content']);

        $this->assertSame('New', $updated->title);
        $this->assertSame('New content', $updated->content);
        $this->assertDatabaseHas('support_snippets', ['id' => $snippet->id, 'title' => 'New', 'content' => 'New content']);
    }
}
