<?php

namespace Tests\Unit\Actions\User\Favorites;

use App\Api\V1\Actions\User\Favorites\SubscribeProductRestockAction;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscribeProductRestockActionTest extends TestCase
{
    use RefreshDatabase;

    private SubscribeProductRestockAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(SubscribeProductRestockAction::class);
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

    public function test_execute_favorites_the_product_and_sets_notify_on_restock(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->action->execute($user, $product);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'notify_on_restock' => true,
        ]);
    }

    public function test_execute_on_an_already_favorited_product_only_flips_the_restock_flag(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->favorites()->attach($product->id, ['notify_on_drop' => true, 'notify_on_restock' => false]);

        $this->action->execute($user, $product);

        $pivot = Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertTrue($pivot->notify_on_restock);
        $this->assertTrue($pivot->notify_on_drop, 'must not disturb the existing price-drop subscription');
        $this->assertCount(1, $user->favorites()->get(), 'must not create a duplicate favorites row');
    }
}
