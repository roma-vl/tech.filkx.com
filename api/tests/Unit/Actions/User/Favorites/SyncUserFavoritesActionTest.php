<?php

namespace Tests\Unit\Actions\User\Favorites;

use App\Api\V1\Actions\User\Favorites\SyncUserFavoritesAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncUserFavoritesActionTest extends TestCase
{
    use RefreshDatabase;

    private SyncUserFavoritesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(SyncUserFavoritesAction::class);
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

    public function test_execute_attaches_the_given_product_ids_without_detaching_existing_ones(): void
    {
        $user = User::factory()->create();
        $existing = $this->makeProduct();
        $user->favorites()->attach($existing->id);
        $new = $this->makeProduct();

        $result = $this->action->execute($user, [$new->id]);

        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $existing->id]);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $new->id]);
        $this->assertCount(2, $result);
    }

    public function test_execute_with_empty_ids_returns_current_favorites_unchanged(): void
    {
        $user = User::factory()->create();
        $existing = $this->makeProduct();
        $user->favorites()->attach($existing->id);

        $result = $this->action->execute($user, []);

        $this->assertCount(1, $result);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $existing->id]);
    }
}
