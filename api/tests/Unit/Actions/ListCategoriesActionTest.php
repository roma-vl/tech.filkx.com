<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\ListCategoriesAction;
use App\Models\Category;
use App\Models\Product;
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

    private function attachActiveProduct(Category $category): void
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $product->categories()->attach($category->id);
    }

    // Categories are pre-seeded by a migration (database/data/categories.json), so assertions
    // below check containment/relative order rather than exact counts or full lists.

    public function test_execute_returns_only_top_level_categories(): void
    {
        $parent = $this->makeCategory(null, 'Parent');
        $child = $this->makeCategory($parent->id, 'Child');
        $this->attachActiveProduct($child);

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertContains($parent->id, $ids);
        $this->assertNotContains($child->id, $ids);
    }

    public function test_execute_orders_top_level_categories_by_order(): void
    {
        $second = $this->makeCategory(null, 'Second', 1000001);
        $first = $this->makeCategory(null, 'First', 1000000);
        $this->attachActiveProduct($second);
        $this->attachActiveProduct($first);

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertLessThan(array_search($second->id, $ids), array_search($first->id, $ids));
    }

    public function test_execute_eager_loads_two_levels_of_nested_children(): void
    {
        $parent = $this->makeCategory(null, 'Parent');
        $child = $this->makeCategory($parent->id, 'Child');
        $grandchild = $this->makeCategory($child->id, 'Grandchild');
        $this->attachActiveProduct($grandchild);

        $result = $this->action->execute();

        $loadedParent = $result->firstWhere('id', $parent->id);
        $loadedChild = $loadedParent->children->firstWhere('id', $child->id);
        $this->assertSame($child->id, $loadedChild->id);
        $this->assertSame($grandchild->id, $loadedChild->children->first()->id);
    }

    public function test_execute_excludes_a_top_level_category_with_no_products_anywhere_in_its_subtree(): void
    {
        $empty = $this->makeCategory(null, 'Empty Top Level');
        $emptyChild = $this->makeCategory($empty->id, 'Empty Child');
        $this->makeCategory($emptyChild->id, 'Empty Grandchild');

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertNotContains($empty->id, $ids);
    }

    public function test_execute_includes_a_parent_whose_only_active_product_is_on_a_grandchild(): void
    {
        $parent = $this->makeCategory(null, 'Parent With Deep Product');
        $child = $this->makeCategory($parent->id, 'Child');
        $grandchild = $this->makeCategory($child->id, 'Grandchild');
        $this->attachActiveProduct($grandchild);

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertContains($parent->id, $ids);
    }

    public function test_execute_excludes_a_child_category_with_no_products_while_keeping_its_non_empty_sibling(): void
    {
        $parent = $this->makeCategory(null, 'Parent With Mixed Children');
        $emptyChild = $this->makeCategory($parent->id, 'Empty Child');
        $nonEmptyChild = $this->makeCategory($parent->id, 'Non Empty Child');
        $this->attachActiveProduct($nonEmptyChild);

        $loadedParent = $this->action->execute()->firstWhere('id', $parent->id);
        $childIds = $loadedParent->children->pluck('id')->all();

        $this->assertNotContains($emptyChild->id, $childIds);
        $this->assertContains($nonEmptyChild->id, $childIds);
    }

    public function test_execute_ignores_a_draft_product_when_deciding_if_a_category_is_empty(): void
    {
        $category = $this->makeCategory(null, 'Draft Only Category');
        $draftProduct = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'draft',
        ]);
        $draftProduct->categories()->attach($category->id);

        $ids = $this->action->execute()->pluck('id')->all();

        $this->assertNotContains($category->id, $ids);
    }
}
