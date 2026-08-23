<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\ListTrashedAdminProductsAction;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTrashedAdminProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListTrashedAdminProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListTrashedAdminProductsAction::class);
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

    public function test_execute_returns_only_soft_deleted_products(): void
    {
        $deleted = $this->makeProduct();
        app(ProductRepository::class)->delete($deleted);
        $active = $this->makeProduct();

        $result = $this->action->execute();

        $this->assertTrue($result->contains('id', $deleted->id));
        $this->assertFalse($result->contains('id', $active->id));
    }
}
