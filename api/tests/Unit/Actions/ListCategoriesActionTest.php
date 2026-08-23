<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\ListCategoriesAction;
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

    private function makeCategory(?int $parentId, string $name, int $order = 0): Category
    {
        return Category::create([
            'parent_id' => $parentId,
            'slug' => 'cat-'.uniqid(),
            'name' => ['uk' => $name, 'en' => $name],
            'order' => $order,
        ]);
    }

    // Categories are pre-seeded by a migration (database/data/categories.json), so assertions
    // below check containment/relative order rather than exact counts or full lists.

    public function test_execute_returns_only_top_level_categories(): void
    {
        $parent = $this->makeCategory(null, 'Parent');
        $child = $this->makeCategory($parent->id, 'Child');

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertContains($parent->id, $ids);
        $this->assertNotContains($child->id, $ids);
    }

    public function test_execute_orders_top_level_categories_by_order(): void
    {
        $second = $this->makeCategory(null, 'Second', 1000001);
        $first = $this->makeCategory(null, 'First', 1000000);

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertLessThan(array_search($second->id, $ids), array_search($first->id, $ids));
    }

    public function test_execute_eager_loads_two_levels_of_nested_children(): void
    {
        $parent = $this->makeCategory(null, 'Parent');
        $child = $this->makeCategory($parent->id, 'Child');
        $grandchild = $this->makeCategory($child->id, 'Grandchild');

        $result = $this->action->execute();

        $loadedParent = $result->firstWhere('id', $parent->id);
        $loadedChild = $loadedParent->children->firstWhere('id', $child->id);
        $this->assertSame($child->id, $loadedChild->id);
        $this->assertSame($grandchild->id, $loadedChild->children->first()->id);
    }
}
