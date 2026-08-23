<?php

namespace Tests\Unit\Actions\User\ViewedProducts;

use App\Api\V1\Actions\User\ViewedProducts\SyncViewedProductsAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncViewedProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private SyncViewedProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(SyncViewedProductsAction::class);
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

    public function test_execute_attaches_new_products_from_the_incoming_items(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $result = $this->action->execute($user, [
            ['id' => $product->id, 'view_count' => 5, 'last_viewed_at' => now()->toISOString()],
        ]);

        $viewed = $user->viewedProducts()->where('product_id', $product->id)->first();
        $this->assertSame(5, $viewed->pivot->view_count);
        $this->assertCount(1, $result);
    }

    public function test_execute_keeps_the_higher_view_count_for_an_existing_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->viewedProducts()->attach($product->id, [
            'view_count' => 10,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->action->execute($user, [
            ['id' => $product->id, 'view_count' => 2, 'last_viewed_at' => now()->toISOString()],
        ]);

        $viewed = $user->viewedProducts()->where('product_id', $product->id)->first();
        $this->assertSame(10, $viewed->pivot->view_count);
    }

    public function test_execute_skips_items_without_an_id(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user, [
            ['view_count' => 1],
        ]);

        $this->assertCount(0, $result);
    }
}
