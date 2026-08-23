<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\BulkDeleteAdminProductsAction;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeleteAdminProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private BulkDeleteAdminProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(BulkDeleteAdminProductsAction::class);
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

    public function test_execute_soft_deletes_every_given_product_and_its_variants(): void
    {
        $first = $this->makeProduct();
        $firstVariant = ProductVariant::create(['product_id' => $first->id, 'sku' => 'sku-'.uniqid(), 'price' => 100]);
        $second = $this->makeProduct();
        $untouched = $this->makeProduct();

        $count = $this->action->execute([$first->id, $second->id]);

        $this->assertSame(2, $count);
        $this->assertNull(Product::find($first->id));
        $this->assertNull(Product::find($second->id));
        $this->assertNotNull(Product::find($untouched->id));
        $this->assertNull(ProductVariant::find($firstVariant->id));
    }

    public function test_execute_ignores_ids_that_do_not_exist(): void
    {
        $product = $this->makeProduct();

        $count = $this->action->execute([$product->id, 999999]);

        $this->assertSame(1, $count);
        $this->assertNull(Product::find($product->id));
    }
}
