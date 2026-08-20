<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\GenerateUniqueBlogPostSlugAction;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateUniqueBlogPostSlugActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateUniqueBlogPostSlugAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateUniqueBlogPostSlugAction::class);
    }

    private function makePost(string $slug): BlogPost
    {
        return BlogPost::create([
            'slug' => $slug,
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ]);
    }

    public function test_execute_slugifies_the_source_when_no_conflict_exists(): void
    {
        $slug = $this->action->execute('Zz Test Post');

        $this->assertSame('zz-test-post', $slug);
    }

    public function test_execute_appends_a_numeric_suffix_when_the_slug_already_exists(): void
    {
        $this->makePost('zz-test-post');

        $slug = $this->action->execute('Zz Test Post');

        $this->assertSame('zz-test-post-1', $slug);
    }

    public function test_execute_ignores_the_excluded_post_when_checking_for_conflicts(): void
    {
        $post = $this->makePost('zz-test-post');

        $slug = $this->action->execute('Zz Test Post', $post->id);

        $this->assertSame('zz-test-post', $slug);
    }

    public function test_execute_avoids_slugs_still_held_by_a_soft_deleted_post(): void
    {
        $post = $this->makePost('zz-test-post');
        $post->delete();

        $slug = $this->action->execute('Zz Test Post');

        $this->assertSame('zz-test-post-1', $slug);
    }

    public function test_execute_falls_back_to_a_generated_slug_when_the_source_has_no_slug_characters(): void
    {
        $slug = $this->action->execute('---');

        $this->assertStringStartsWith('post-', $slug);
    }
}
