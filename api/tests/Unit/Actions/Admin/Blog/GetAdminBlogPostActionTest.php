<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\GetAdminBlogPostAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAdminBlogPostActionTest extends TestCase
{
    use RefreshDatabase;

    private GetAdminBlogPostAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetAdminBlogPostAction::class);
    }

    public function test_execute_returns_the_post_with_its_relations_loaded(): void
    {
        $post = BlogPost::create([
            'slug' => 'zz-test-post',
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ]);

        $result = $this->action->execute($post->id);

        $this->assertTrue($result->is($post));
        $this->assertTrue($result->relationLoaded('category'));
        $this->assertTrue($result->relationLoaded('author'));
        $this->assertTrue($result->relationLoaded('tags'));
    }

    public function test_execute_throws_when_the_post_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
