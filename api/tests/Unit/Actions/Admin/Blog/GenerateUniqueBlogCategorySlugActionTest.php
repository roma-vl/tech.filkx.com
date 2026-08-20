<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\GenerateUniqueBlogCategorySlugAction;
use App\Models\BlogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateUniqueBlogCategorySlugActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateUniqueBlogCategorySlugAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateUniqueBlogCategorySlugAction::class);
    }

    public function test_execute_slugifies_the_source_when_no_conflict_exists(): void
    {
        $slug = $this->action->execute('Zz Test News');

        $this->assertSame('zz-test-news', $slug);
    }

    public function test_execute_appends_a_numeric_suffix_when_the_slug_already_exists(): void
    {
        BlogCategory::create(['slug' => 'zz-test-news', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $slug = $this->action->execute('Zz Test News');

        $this->assertSame('zz-test-news-1', $slug);
    }

    public function test_execute_ignores_the_excluded_category_when_checking_for_conflicts(): void
    {
        $category = BlogCategory::create(['slug' => 'zz-test-news', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $slug = $this->action->execute('Zz Test News', $category->id);

        $this->assertSame('zz-test-news', $slug);
    }
}
