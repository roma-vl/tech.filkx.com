<?php

namespace Tests\Unit\Actions\Admin\Category;

use App\Api\Admin\Actions\Category\ListCategoriesAction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListCategoriesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListCategoriesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListCategoriesAction::class);
    }

    public function test_execute_returns_all_categories_with_their_parent_loaded(): void
    {
        $baseline = Category::count();
        $parent = Category::create(['slug' => 'zz-test-parent', 'name' => ['uk' => 'Батько', 'en' => 'Parent']]);
        Category::create(['slug' => 'zz-test-child', 'name' => ['uk' => 'Дитина', 'en' => 'Child'], 'parent_id' => $parent->id]);

        $categories = $this->action->execute();

        $this->assertCount($baseline + 2, $categories);
        $child = $categories->firstWhere('slug', 'zz-test-child');
        $this->assertTrue($child->relationLoaded('parent'));
        $this->assertSame($parent->id, $child->parent->id);
    }
}
