<?php

namespace Tests\Unit\Actions\Blog;

use App\Api\V1\Actions\Blog\ListBlogTagsAction;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBlogTagsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListBlogTagsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListBlogTagsAction::class);
    }

    private function makePost(string $status = 'published'): BlogPost
    {
        return BlogPost::create([
            'slug' => 'post-'.uniqid(),
            'title' => ['uk' => 'A', 'en' => 'A'],
            'content' => ['uk' => 'A', 'en' => 'A'],
            'status' => $status,
        ]);
    }

    public function test_execute_excludes_tags_with_no_published_posts(): void
    {
        $unusedTag = BlogTag::create(['slug' => 'unused', 'name' => ['uk' => 'Unused', 'en' => 'Unused']]);
        $draftOnlyTag = BlogTag::create(['slug' => 'draft-only', 'name' => ['uk' => 'Draft', 'en' => 'Draft']]);
        $this->makePost('draft')->tags()->attach($draftOnlyTag->id);

        $result = $this->action->execute();

        $this->assertCount(0, $result);
    }

    public function test_execute_orders_tags_by_post_count_descending(): void
    {
        $popular = BlogTag::create(['slug' => 'popular', 'name' => ['uk' => 'Popular', 'en' => 'Popular']]);
        $rare = BlogTag::create(['slug' => 'rare', 'name' => ['uk' => 'Rare', 'en' => 'Rare']]);

        $this->makePost()->tags()->attach($popular->id);
        $this->makePost()->tags()->attach($popular->id);
        $this->makePost()->tags()->attach($rare->id);

        $result = $this->action->execute();

        $this->assertSame('popular', $result->first()['slug']);
        $this->assertSame(2, $result->first()['postsCount']);
    }
}
