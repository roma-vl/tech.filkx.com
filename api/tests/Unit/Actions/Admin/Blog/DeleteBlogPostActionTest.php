<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\DeleteBlogPostAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBlogPostActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteBlogPostAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteBlogPostAction::class);
    }

    public function test_execute_soft_deletes_the_post(): void
    {
        $post = BlogPost::create([
            'slug' => 'zz-test-post',
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ]);

        $this->action->execute($post->id);

        $this->assertSoftDeleted('blog_posts', ['id' => $post->id]);
    }

    public function test_execute_throws_when_the_post_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
