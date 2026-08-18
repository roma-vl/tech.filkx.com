<?php

namespace Tests\Unit\Actions\Blog;

use App\Api\V1\Actions\Blog\FormatBlogPostAction;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormatBlogPostActionTest extends TestCase
{
    use RefreshDatabase;

    private FormatBlogPostAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(FormatBlogPostAction::class);
    }

    private function makePost(): BlogPost
    {
        $post = BlogPost::create([
            'slug' => 'post-'.uniqid(),
            'title' => ['uk' => 'Заголовок', 'en' => 'Title'],
            'excerpt' => ['uk' => 'Уривок', 'en' => 'An excerpt'],
            'content' => ['uk' => 'Текст', 'en' => 'Body'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        // `views` is intentionally not mass-assignable; only `increment()` is meant to change it.
        $post->increment('views', 5);

        return $post;
    }

    public function test_execute_omits_content_by_default(): void
    {
        $post = $this->makePost();

        $result = $this->action->execute($post);

        $this->assertArrayNotHasKey('content', $result);
        $this->assertSame($post->slug, $result['slug']);
        $this->assertSame(5, $result['views']);
    }

    public function test_execute_includes_content_when_requested(): void
    {
        $post = $this->makePost();

        $result = $this->action->execute($post, withContent: true);

        $this->assertArrayHasKey('content', $result);
        $this->assertSame($post->content, $result['content']);
    }

    public function test_execute_returns_null_category_and_author_when_absent(): void
    {
        $post = $this->makePost();

        $result = $this->action->execute($post);

        $this->assertNull($result['category']);
        $this->assertNull($result['author']);
        $this->assertSame([], $result['tags']->all());
    }
}
