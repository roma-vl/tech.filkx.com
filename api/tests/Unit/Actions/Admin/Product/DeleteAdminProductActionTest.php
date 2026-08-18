<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\DeleteAdminProductAction;
use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdminProductActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAdminProductAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteAdminProductAction::class);
    }

    public function test_execute_deletes_the_product(): void
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $this->action->execute($product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_execute_throws_when_the_product_does_not_exist(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->action->execute(999999);
    }
}
