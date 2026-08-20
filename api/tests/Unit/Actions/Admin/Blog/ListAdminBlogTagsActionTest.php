<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\ListAdminBlogTagsAction;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminBlogTagsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminBlogTagsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminBlogTagsAction::class);
    }

    public function test_execute_returns_tags_newest_first_with_a_posts_count(): void
    {
        $first = BlogTag::create(['slug' => 'zz-test-a', 'name' => ['uk' => 'А', 'en' => 'A']]);
        $second = BlogTag::create(['slug' => 'zz-test-b', 'name' => ['uk' => 'Б', 'en' => 'B']]);
        $post = BlogPost::create([
            'slug' => 'zz-test-post',
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ]);
        $post->tags()->attach($first->id);

        $tags = $this->action->execute();

        $this->assertSame([$second->id, $first->id], $tags->pluck('id')->all());
        $this->assertSame(1, $tags->firstWhere('id', $first->id)->posts_count);
        $this->assertSame(0, $tags->firstWhere('id', $second->id)->posts_count);
    }
}
