<?php

namespace Tests\Unit\Actions\User\ViewedProducts;

use App\Api\V1\Actions\User\ViewedProducts\TrackViewedProductAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackViewedProductActionTest extends TestCase
{
    use RefreshDatabase;

    private TrackViewedProductAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(TrackViewedProductAction::class);
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

    public function test_execute_attaches_a_new_view_with_count_one(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->action->execute($user, $product->id);

        $viewed = $user->viewedProducts()->where('product_id', $product->id)->first();
        $this->assertSame(1, $viewed->pivot->view_count);
    }

    public function test_execute_increments_the_view_count_on_repeat_views(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->viewedProducts()->attach($product->id, [
            'view_count' => 2,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $this->action->execute($user, $product->id);

        $viewed = $user->viewedProducts()->where('product_id', $product->id)->first();
        $this->assertSame(3, $viewed->pivot->view_count);
    }
}
