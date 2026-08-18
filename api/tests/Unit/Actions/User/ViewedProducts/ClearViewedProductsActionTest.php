<?php

namespace Tests\Unit\Actions\User\ViewedProducts;

use App\Api\V1\Actions\User\ViewedProducts\ClearViewedProductsAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearViewedProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private ClearViewedProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ClearViewedProductsAction::class);
    }

    public function test_execute_detaches_all_viewed_products(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $user->viewedProducts()->attach($product->id, ['view_count' => 1]);

        $this->action->execute($user);

        $this->assertDatabaseMissing('product_views', ['user_id' => $user->id]);
    }
}
