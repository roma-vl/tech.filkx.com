<?php

namespace Tests\Unit\Actions\Blog;

use App\Api\V1\Actions\Blog\ListBlogCategoriesAction;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBlogCategoriesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListBlogCategoriesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListBlogCategoriesAction::class);
    }

    public function test_execute_includes_categories_with_no_posts(): void
    {
        BlogCategory::create(['slug' => 'empty', 'name' => ['uk' => 'Порожня', 'en' => 'Empty'], 'order' => 1]);

        $result = $this->action->execute();

        $this->assertCount(1, $result);
        $this->assertSame(0, $result->first()['postsCount']);
    }

    public function test_execute_only_counts_published_posts(): void
    {
        $category = BlogCategory::create(['slug' => 'news', 'name' => ['uk' => 'Новини', 'en' => 'News'], 'order' => 1]);
        BlogPost::create([
            'blog_category_id' => $category->id,
            'slug' => 'p1',
            'title' => ['uk' => 'A', 'en' => 'A'],
            'content' => ['uk' => 'A', 'en' => 'A'],
            'status' => 'published',
        ]);
        BlogPost::create([
            'blog_category_id' => $category->id,
            'slug' => 'p2',
            'title' => ['uk' => 'B', 'en' => 'B'],
            'content' => ['uk' => 'B', 'en' => 'B'],
            'status' => 'draft',
        ]);

        $result = $this->action->execute();

        $this->assertSame(1, $result->first()['postsCount']);
    }
}
