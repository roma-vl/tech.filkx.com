<?php

namespace Tests\Unit\Actions\Blog;

use App\Api\V1\Actions\Blog\GetBlogPostAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetBlogPostActionTest extends TestCase
{
    use RefreshDatabase;

    private GetBlogPostAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetBlogPostAction::class);
    }

    private function makePost(array $overrides = []): BlogPost
    {
        $initialViews = $overrides['views'] ?? 0;
        unset($overrides['views']);

        $post = BlogPost::create(array_merge([
            'slug' => 'post-'.uniqid(),
            'title' => ['uk' => 'Заголовок', 'en' => 'Title'],
            'content' => ['uk' => 'Текст', 'en' => 'Body'],
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));

        // `views` is intentionally not mass-assignable; only `increment()` is meant to change it.
        if ($initialViews > 0) {
            $post->increment('views', $initialViews);
        }

        return $post;
    }

    public function test_execute_returns_the_post_with_content(): void
    {
        $post = $this->makePost();

        $result = $this->action->execute($post->slug);

        $this->assertSame($post->slug, $result['slug']);
        $this->assertArrayHasKey('content', $result);
    }

    public function test_execute_increments_the_view_count(): void
    {
        $post = $this->makePost(['views' => 3]);

        $result = $this->action->execute($post->slug);

        $this->assertSame(4, $result['views']);
        $this->assertSame(4, $post->fresh()->views);
    }

    public function test_execute_throws_a_404_for_an_unpublished_post(): void
    {
        $post = $this->makePost(['status' => 'draft']);

        $this->expectException(ModelNotFoundException::class);

        $this->action->execute($post->slug);
    }

    public function test_execute_throws_a_404_for_an_unknown_slug(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute('does-not-exist');
    }
}
