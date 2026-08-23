<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\BulkUpdateProductCategoryAction;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUpdateProductCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private BulkUpdateProductCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(BulkUpdateProductCategoryAction::class);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    private function makeCategory(string $name): Category
    {
        return Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => $name, 'en' => $name], 'order' => 0]);
    }

    public function test_execute_moves_every_given_product_into_the_new_category(): void
    {
        $oldCategory = $this->makeCategory('Old');
        $newCategory = $this->makeCategory('New');
        $first = $this->makeProduct();
        $first->categories()->attach($oldCategory->id);
        $second = $this->makeProduct();

        $count = $this->action->execute([$first->id, $second->id], $newCategory->id);

        $this->assertSame(2, $count);
        $this->assertEqualsCanonicalizing([$newCategory->id], $first->categories()->pluck('categories.id')->all());
        $this->assertEqualsCanonicalizing([$newCategory->id], $second->categories()->pluck('categories.id')->all());
    }
}
