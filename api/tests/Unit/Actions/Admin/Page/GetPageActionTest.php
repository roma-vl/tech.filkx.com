<?php

namespace Tests\Unit\Actions\Admin\Page;

use App\Api\Admin\Actions\Page\GetPageAction;
use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetPageActionTest extends TestCase
{
    use RefreshDatabase;

    private GetPageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetPageAction::class);
    }

    public function test_execute_returns_the_page_by_id(): void
    {
        $page = Page::create([
            'slug' => 'about-us',
            'title' => ['uk' => 'Про нас', 'en' => 'About us'],
            'content' => ['uk' => 'Зміст', 'en' => 'Content'],
            'status' => 'published',
        ]);

        $result = $this->action->execute($page->id);

        $this->assertTrue($result->is($page));
    }

    public function test_execute_throws_when_the_page_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
