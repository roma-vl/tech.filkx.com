<?php

namespace Tests\Unit\Actions\User\Favorites;

use App\Api\V1\Actions\User\Favorites\GetUserFavoritesAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserFavoritesActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserFavoritesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserFavoritesAction::class);
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

    public function test_execute_returns_the_users_favorited_active_products(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->favorites()->attach($product->id);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame($product->id, $result->first()->id);
    }

    public function test_execute_excludes_inactive_products(): void
    {
        $user = User::factory()->create();
        $inactive = $this->makeProduct('draft');
        $user->favorites()->attach($inactive->id);

        $result = $this->action->execute($user);

        $this->assertCount(0, $result);
    }

    public function test_execute_returns_empty_collection_when_user_has_no_favorites(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user);

        $this->assertCount(0, $result);
    }
}
