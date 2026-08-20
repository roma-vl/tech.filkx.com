<?php

namespace Tests\Unit\Actions\Blog;

use App\Api\V1\Actions\Blog\ListBlogPostsAction;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBlogPostsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListBlogPostsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListBlogPostsAction::class);
    }

    private function makePost(array $overrides = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'slug' => 'post-'.uniqid(),
            'title' => ['uk' => 'Заголовок '.uniqid(), 'en' => 'Title'],
            'content' => ['uk' => 'Текст', 'en' => 'Body'],
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));
    }

    public function test_execute_only_returns_published_posts(): void
    {
        $this->makePost(['status' => 'published']);
        $this->makePost(['status' => 'draft']);

        $result = $this->action->execute([], 20);

        $this->assertCount(1, $result['data']);
        $this->assertSame(1, $result['meta']['total']);
    }

    public function test_execute_filters_by_category_slug(): void
    {
        $category = BlogCategory::create(['slug' => 'news', 'name' => ['uk' => 'Новини', 'en' => 'News']]);
        $otherCategory = BlogCategory::create(['slug' => 'reviews', 'name' => ['uk' => 'Огляди', 'en' => 'Reviews']]);
        $this->makePost(['blog_category_id' => $category->id]);
        $this->makePost(['blog_category_id' => $otherCategory->id]);

        $result = $this->action->execute(['category' => 'news'], 20);

        $this->assertCount(1, $result['data']);
    }

    public function test_execute_filters_by_tag_slug(): void
    {
        $tag = BlogTag::create(['slug' => 'php', 'name' => ['uk' => 'PHP', 'en' => 'PHP']]);
        $taggedPost = $this->makePost();
        $taggedPost->tags()->attach($tag->id);
        $this->makePost();

        $result = $this->action->execute(['tag' => 'php'], 20);

        $this->assertCount(1, $result['data']);
    }

    public function test_execute_paginates_using_the_given_page_size(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makePost();
        }

        $result = $this->action->execute([], 2);

        $this->assertCount(2, $result['data']);
        $this->assertSame(3, $result['meta']['total']);
        $this->assertSame(2, $result['meta']['last_page']);
    }
}
