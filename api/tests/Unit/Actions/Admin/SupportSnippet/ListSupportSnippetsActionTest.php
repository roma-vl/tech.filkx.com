<?php

namespace Tests\Unit\Actions\Admin\SupportSnippet;

use App\Api\Admin\Actions\SupportSnippet\ListSupportSnippetsAction;
use App\Models\SupportSnippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSupportSnippetsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListSupportSnippetsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListSupportSnippetsAction::class);
    }

    public function test_execute_returns_snippets_newest_first(): void
    {
        $older = SupportSnippet::create(['title' => 'Old', 'content' => 'Old content']);
        $older->created_at = now()->subDay();
        $older->save();
        $newer = SupportSnippet::create(['title' => 'New', 'content' => 'New content']);

        $result = $this->action->execute();

        $this->assertSame($newer->id, $result->first()->id);
        $this->assertSame($older->id, $result->last()->id);
    }

    public function test_execute_returns_an_empty_collection_when_there_are_no_snippets(): void
    {
        $result = $this->action->execute();

        $this->assertCount(0, $result);
    }
}
