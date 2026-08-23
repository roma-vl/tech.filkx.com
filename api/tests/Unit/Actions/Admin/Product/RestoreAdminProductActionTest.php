<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\RestoreAdminProductAction;
use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Api\V1\Exceptions\ProductSlugConflictException;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestoreAdminProductActionTest extends TestCase
{
    use RefreshDatabase;

    private RestoreAdminProductAction $action;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RestoreAdminProductAction::class);
        $this->repository = app(ProductRepository::class);
    }

    private function makeProduct(string $slug): Product
    {
        return Product::create([
            'slug' => $slug,
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_execute_restores_a_soft_deleted_product_and_its_variants(): void
    {
        $product = $this->makeProduct('restorable-product');
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'sku-'.uniqid(), 'price' => 100]);
        $this->repository->delete($product);

        $restored = $this->action->execute($product->id);

        $this->assertSame($product->id, $restored->id);
        $this->assertNotNull(Product::find($product->id));
        $this->assertNotNull(ProductVariant::find($variant->id));
    }

    public function test_execute_throws_when_the_product_is_not_in_the_trash(): void
    {
        $product = $this->makeProduct('active-product');

        $this->expectException(ProductNotFoundException::class);

        $this->action->execute($product->id);
    }

    public function test_execute_throws_when_the_id_does_not_exist_at_all(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->action->execute(999999);
    }

    public function test_execute_throws_when_the_slug_has_been_claimed_by_another_product_since(): void
    {
        $product = $this->makeProduct('shared-slug');
        $this->repository->delete($product);
        $this->makeProduct('shared-slug');

        $this->expectException(ProductSlugConflictException::class);

        $this->action->execute($product->id);
    }
}
