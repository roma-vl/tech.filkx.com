<?php

namespace Tests\Feature\Wishlist;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProductWithVariant(float $price): Product
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
        ]);

        return $product;
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/wishlist')->assertStatus(401);
    }

    public function test_index_lists_the_users_wishlist_items(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProductWithVariant(500);
        $user->favorites()->attach($product->id, ['price_at_add' => 500, 'notify_on_drop' => true]);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/v1/wishlist');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $product->slug)
            ->assertJsonPath('data.0.price_at_add', 500);
    }

    public function test_add_adds_a_product_and_snapshots_its_current_price(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProductWithVariant(750);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/v1/wishlist/{$product->id}");

        $response->assertOk()->assertJsonPath('notify', true);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'notify_on_drop' => 1,
        ]);
    }

    public function test_add_respects_the_notify_on_drop_flag(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProductWithVariant(750);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/v1/wishlist/{$product->id}", ['notify_on_drop' => false]);

        $response->assertOk()->assertJsonPath('notify', false);
    }

    public function test_remove_deletes_the_wishlist_entry(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProductWithVariant(500);
        $user->favorites()->attach($product->id, ['price_at_add' => 500, 'notify_on_drop' => true]);

        $response = $this->withHeaders($this->authHeader($user))
            ->deleteJson("/api/v1/wishlist/{$product->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_toggle_notify_flips_the_notification_preference(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProductWithVariant(500);
        $user->favorites()->attach($product->id, ['price_at_add' => 500, 'notify_on_drop' => true]);

        $response = $this->withHeaders($this->authHeader($user))
            ->patchJson("/api/v1/wishlist/{$product->id}/notify");

        $response->assertOk()->assertJsonPath('notify_on_drop', false);
        $this->assertFalse(Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->first()->notify_on_drop);
    }
}
