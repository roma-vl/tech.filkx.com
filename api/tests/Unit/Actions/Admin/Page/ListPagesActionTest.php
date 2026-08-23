<?php

namespace Tests\Unit\Actions\Admin\Page;

use App\Api\Admin\Actions\Page\ListPagesAction;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPagesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListPagesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListPagesAction::class);
    }

    private function makePage(string $slug, array $title): Page
    {
        return Page::create([
            'slug' => $slug,
            'title' => $title,
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'published',
        ]);
    }

    public function test_execute_returns_pages_paginated_newest_first(): void
    {
        $baseline = Page::count();
        $this->makePage('zz-test-first', ['uk' => 'Перша', 'en' => 'First']);
        $second = $this->makePage('zz-test-second', ['uk' => 'Друга', 'en' => 'Second']);

        $result = $this->action->execute(null, 20);

        $this->assertSame($baseline + 2, $result->total());
        $this->assertSame($second->id, $result->items()[0]->id);
    }

    public function test_execute_respects_the_per_page_limit(): void
    {
        $baseline = Page::count();
        $this->makePage('zz-test-a', ['uk' => 'A', 'en' => 'A']);
        $this->makePage('zz-test-b', ['uk' => 'B', 'en' => 'B']);
        $this->makePage('zz-test-c', ['uk' => 'C', 'en' => 'C']);

        $result = $this->action->execute(null, 2);

        $this->assertCount(2, $result->items());
        $this->assertSame($baseline + 3, $result->total());
    }
}
