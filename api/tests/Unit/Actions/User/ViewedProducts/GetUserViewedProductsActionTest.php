<?php

namespace Tests\Unit\Actions\User\ViewedProducts;

use App\Api\V1\Actions\User\ViewedProducts\GetUserViewedProductsAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserViewedProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserViewedProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserViewedProductsAction::class);
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

    public function test_execute_returns_products_ordered_by_most_recently_viewed(): void
    {
        $user = User::factory()->create();
        $older = $this->makeProduct();
        $newer = $this->makeProduct();

        $user->viewedProducts()->attach($older->id, [
            'view_count' => 1,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $user->viewedProducts()->attach($newer->id, [
            'view_count' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->action->execute($user);

        $this->assertCount(2, $result);
        $this->assertSame($newer->id, $result->first()->id);
        $this->assertSame(3, $result->first()->view_count);
        $this->assertSame($older->id, $result->last()->id);
    }

    public function test_execute_returns_empty_collection_when_nothing_viewed(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user);

        $this->assertCount(0, $result);
    }
}
