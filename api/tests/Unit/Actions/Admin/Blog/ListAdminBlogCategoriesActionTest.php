<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\ListAdminBlogCategoriesAction;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminBlogCategoriesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminBlogCategoriesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminBlogCategoriesAction::class);
    }

    public function test_execute_returns_categories_ordered_and_with_a_posts_count(): void
    {
        $second = BlogCategory::create(['slug' => 'zz-test-b', 'name' => ['uk' => 'Б', 'en' => 'B'], 'order' => 2]);
        $first = BlogCategory::create(['slug' => 'zz-test-a', 'name' => ['uk' => 'А', 'en' => 'A'], 'order' => 1]);
        BlogPost::create([
            'blog_category_id' => $first->id,
            'slug' => 'zz-test-post',
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ]);

        $categories = $this->action->execute();

        $this->assertSame([$first->id, $second->id], $categories->pluck('id')->all());
        $this->assertSame(1, $categories->firstWhere('id', $first->id)->posts_count);
        $this->assertSame(0, $categories->firstWhere('id', $second->id)->posts_count);
    }
}
