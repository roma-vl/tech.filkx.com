<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\GenerateUniqueBlogTagSlugAction;
use App\Models\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateUniqueBlogTagSlugActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateUniqueBlogTagSlugAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateUniqueBlogTagSlugAction::class);
    }

    public function test_execute_slugifies_the_source_when_no_conflict_exists(): void
    {
        $slug = $this->action->execute('Zz Test Tag');

        $this->assertSame('zz-test-tag', $slug);
    }

    public function test_execute_appends_a_numeric_suffix_when_the_slug_already_exists(): void
    {
        BlogTag::create(['slug' => 'zz-test-tag', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $slug = $this->action->execute('Zz Test Tag');

        $this->assertSame('zz-test-tag-1', $slug);
    }

    public function test_execute_ignores_the_excluded_tag_when_checking_for_conflicts(): void
    {
        $tag = BlogTag::create(['slug' => 'zz-test-tag', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $slug = $this->action->execute('Zz Test Tag', $tag->id);

        $this->assertSame('zz-test-tag', $slug);
    }
}
