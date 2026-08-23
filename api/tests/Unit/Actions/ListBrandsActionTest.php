<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\ListBrandsAction;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBrandsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListBrandsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListBrandsAction::class);
    }

    private function makeProduct(Brand $brand, string $status = 'active'): Product
    {
        return Product::create([
            'brand_id' => $brand->id,
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    public function test_execute_counts_only_active_products_per_brand(): void
    {
        $brand = Brand::create(['name' => 'Brand', 'slug' => 'brand-'.uniqid()]);
        $this->makeProduct($brand, 'active');
        $this->makeProduct($brand, 'active');
        $this->makeProduct($brand, 'inactive');

        $result = $this->action->execute();

        $this->assertSame(2, $result->firstWhere('id', $brand->id)->products_count);
    }

    public function test_execute_orders_brands_alphabetically_by_name(): void
    {
        Brand::create(['name' => 'Zebra', 'slug' => 'zebra-'.uniqid()]);
        Brand::create(['name' => 'Alpha', 'slug' => 'alpha-'.uniqid()]);

        $result = $this->action->execute();

        $this->assertSame(['Alpha', 'Zebra'], $result->pluck('name')->all());
    }

    public function test_execute_includes_brands_with_no_products(): void
    {
        $brand = Brand::create(['name' => 'Empty Brand', 'slug' => 'empty-brand-'.uniqid()]);

        $result = $this->action->execute();

        $this->assertSame(0, $result->firstWhere('id', $brand->id)->products_count);
    }

    public function test_execute_scopes_the_product_count_to_the_given_category(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $otherCategory = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $brand = Brand::create(['name' => 'Brand', 'slug' => 'brand-'.uniqid()]);
        $inCategory = $this->makeProduct($brand, 'active');
        $inCategory->categories()->attach($category->id);
        $outsideCategory = $this->makeProduct($brand, 'active');
        $outsideCategory->categories()->attach($otherCategory->id);

        $result = $this->action->execute($category->slug);

        $this->assertSame(1, $result->firstWhere('id', $brand->id)->products_count);
    }
}
