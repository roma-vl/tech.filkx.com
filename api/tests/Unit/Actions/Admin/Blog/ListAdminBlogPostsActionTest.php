<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\ListAdminBlogPostsAction;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminBlogPostsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminBlogPostsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminBlogPostsAction::class);
    }

    private function makePost(array $overrides = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'slug' => 'zz-test-'.uniqid(),
            'title' => ['uk' => 'Заголовок', 'en' => 'Title'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ], $overrides));
    }

    public function test_execute_returns_posts_paginated_newest_first(): void
    {
        $this->makePost();
        $second = $this->makePost();

        $result = $this->action->execute(null, null, null, 20);

        $this->assertSame(2, $result->total());
        $this->assertSame($second->id, $result->items()[0]->id);
    }

    public function test_execute_filters_by_status(): void
    {
        $this->makePost(['status' => 'draft']);
        $published = $this->makePost(['status' => 'published']);

        $result = $this->action->execute('published', null, null, 20);

        $this->assertSame(1, $result->total());
        $this->assertSame($published->id, $result->items()[0]->id);
    }

    public function test_execute_filters_by_category(): void
    {
        $category = BlogCategory::create(['slug' => 'zz-test-cat', 'name' => ['uk' => 'Т', 'en' => 'T']]);
        $inCategory = $this->makePost(['blog_category_id' => $category->id]);
        $this->makePost();

        $result = $this->action->execute(null, $category->id, null, 20);

        $this->assertSame(1, $result->total());
        $this->assertSame($inCategory->id, $result->items()[0]->id);
    }

    public function test_execute_respects_the_per_page_limit(): void
    {
        $this->makePost();
        $this->makePost();
        $this->makePost();

        $result = $this->action->execute(null, null, null, 2);

        $this->assertCount(2, $result->items());
        $this->assertSame(3, $result->total());
    }
}
