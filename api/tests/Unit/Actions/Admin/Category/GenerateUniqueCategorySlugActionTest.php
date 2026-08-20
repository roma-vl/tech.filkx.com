<?php

namespace Tests\Unit\Actions\Admin\Category;

use App\Api\Admin\Actions\Category\GenerateUniqueCategorySlugAction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateUniqueCategorySlugActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateUniqueCategorySlugAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateUniqueCategorySlugAction::class);
    }

    private function makeCategory(string $slug): Category
    {
        return Category::create([
            'slug' => $slug,
            'name' => ['uk' => 'Т', 'en' => 'T'],
        ]);
    }

    public function test_execute_slugifies_the_source_when_no_conflict_exists(): void
    {
        $slug = $this->action->execute('Zz Test Phones');

        $this->assertSame('zz-test-phones', $slug);
    }

    public function test_execute_appends_a_numeric_suffix_when_the_slug_already_exists(): void
    {
        $this->makeCategory('zz-test-phones');

        $slug = $this->action->execute('Zz Test Phones');

        $this->assertSame('zz-test-phones-1', $slug);
    }

    public function test_execute_ignores_the_excluded_category_when_checking_for_conflicts(): void
    {
        $category = $this->makeCategory('zz-test-phones');

        $slug = $this->action->execute('Zz Test Phones', $category->id);

        $this->assertSame('zz-test-phones', $slug);
    }

    public function test_execute_falls_back_to_a_generated_slug_when_the_source_has_no_slug_characters(): void
    {
        $slug = $this->action->execute('---');

        $this->assertStringStartsWith('category-', $slug);
    }
}
