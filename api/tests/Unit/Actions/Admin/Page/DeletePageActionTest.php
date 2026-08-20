<?php

namespace Tests\Unit\Actions\Admin\Page;

use App\Api\Admin\Actions\Page\DeletePageAction;
use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletePageActionTest extends TestCase
{
    use RefreshDatabase;

    private DeletePageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeletePageAction::class);
    }

    public function test_execute_deletes_the_page(): void
    {
        $page = Page::create([
            'slug' => 'zz-test-to-delete',
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'published',
        ]);

        $this->action->execute($page->id);

        $this->assertDatabaseMissing('static_pages', ['id' => $page->id]);
    }

    public function test_execute_throws_when_the_page_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
