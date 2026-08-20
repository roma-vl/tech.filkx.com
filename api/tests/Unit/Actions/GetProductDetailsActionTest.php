<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\GetProductDetailsAction;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class GetProductDetailsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetProductDetailsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetProductDetailsAction::class);
    }

    private function makeProduct(string $status = 'active'): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    public function test_execute_returns_the_product_and_increments_its_view_count(): void
    {
        $product = $this->makeProduct();

        $result = $this->action->execute($product->slug);

        $this->assertSame($product->id, $result->id);
        $this->assertSame(1, $result->views_count);
        $this->assertSame(1, $product->fresh()->views_count);
    }

    public function test_execute_increments_the_view_count_on_every_call(): void
    {
        $product = $this->makeProduct();

        $this->action->execute($product->slug);
        $this->action->execute($product->slug);

        $this->assertSame(2, $product->fresh()->views_count);
    }

    public function test_execute_throws_a_404_for_an_unknown_slug(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->action->execute('does-not-exist');
    }

    public function test_execute_throws_a_404_for_an_inactive_product(): void
    {
        $product = $this->makeProduct('inactive');

        $this->expectException(NotFoundHttpException::class);

        $this->action->execute($product->slug);
    }
}
