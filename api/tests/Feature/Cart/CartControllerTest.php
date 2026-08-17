<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price, int $stock, string $status = 'active', ?float $oldPrice = null): ProductVariant
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
            'old_price' => $oldPrice,
        ]);

        $warehouse = Warehouse::create(['name' => 'Main']);
        Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $stock,
            'reserved' => 0,
        ]);

        return $variant;
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_show_returns_empty_cart_for_a_new_guest_session(): void
    {
        $response = $this->withHeader('X-Cart-Session-ID', 'session-'.uniqid())
            ->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.total', 0)
            ->assertJsonPath('data.promotionDiscount', 0)
            ->assertJsonPath('data.discountedTotal', 0);
    }

    public function test_add_item_adds_it_to_the_cart_and_returns_it_in_show(): void
    {
        $variant = $this->makeVariant(100, 10);
        $session = 'session-'.uniqid();

        $response = $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 2]);

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.variantId', $variant->id)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.price', 100)
            ->assertJsonPath('data.items.0.subtotal', 200)
            ->assertJsonPath('data.total', 200);
    }

    public function test_add_item_rejects_an_out_of_stock_variant(): void
    {
        $variant = $this->makeVariant(100, 0);

        $response = $this->withHeader('X-Cart-Session-ID', 'session-'.uniqid())
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_add_item_rejects_an_unknown_variant(): void
    {
        $response = $this->withHeader('X-Cart-Session-ID', 'session-'.uniqid())
            ->postJson('/api/v1/cart', ['variantId' => 999999, 'quantity' => 1]);

        $response->assertStatus(404);
    }

    public function test_add_item_clamps_quantity_to_available_stock(): void
    {
        $variant = $this->makeVariant(50, 3);

        $response = $this->withHeader('X-Cart-Session-ID', 'session-'.uniqid())
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 10]);

        $response->assertOk()
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.items.0.stock', 3);
    }

    public function test_get_cart_silently_drops_an_item_whose_product_became_inactive(): void
    {
        $variant = $this->makeVariant(100, 5);
        $session = 'session-'.uniqid();

        $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1])
            ->assertOk();

        $variant->product->update(['status' => 'inactive']);

        $response = $this->withHeader('X-Cart-Session-ID', $session)->getJson('/api/v1/cart');

        $response->assertOk()->assertJsonPath('data.items', []);
        $this->assertDatabaseMissing('cart_items', ['variant_id' => $variant->id]);
    }

    public function test_get_cart_silently_drops_an_item_whose_stock_dropped_to_zero(): void
    {
        $variant = $this->makeVariant(100, 5);
        $session = 'session-'.uniqid();

        $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 2])
            ->assertOk();

        $variant->stocks()->first()->update(['reserved' => 5]);

        $response = $this->withHeader('X-Cart-Session-ID', $session)->getJson('/api/v1/cart');

        $response->assertOk()->assertJsonPath('data.items', []);
        $this->assertDatabaseMissing('cart_items', ['variant_id' => $variant->id]);
    }

    public function test_get_cart_clamps_quantity_when_stock_falls_below_the_cart_quantity(): void
    {
        $variant = $this->makeVariant(100, 5);
        $session = 'session-'.uniqid();

        $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 5])
            ->assertOk();

        // Simulate stock being partially reserved by another order placed after this item was added.
        $variant->stocks()->first()->update(['reserved' => 3]);

        $response = $this->withHeader('X-Cart-Session-ID', $session)->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.stock', 2);

        $itemId = $response->json('data.items.0.id');
        $this->assertDatabaseHas('cart_items', ['id' => $itemId, 'quantity' => 2]);
    }

    public function test_guest_sessions_are_isolated_by_the_session_header(): void
    {
        $variant = $this->makeVariant(100, 5);

        $this->withHeader('X-Cart-Session-ID', 'session-a')
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1])
            ->assertOk();

        $response = $this->withHeader('X-Cart-Session-ID', 'session-b')->getJson('/api/v1/cart');

        $response->assertOk()->assertJsonPath('data.items', []);
    }

    public function test_session_can_be_identified_via_the_body_session_id_when_no_header_is_sent(): void
    {
        $variant = $this->makeVariant(100, 5);
        $sessionId = 'session-'.uniqid();

        $this->postJson('/api/v1/cart', [
            'variantId' => $variant->id,
            'quantity' => 1,
            'sessionId' => $sessionId,
        ])->assertOk();

        $response = $this->getJson('/api/v1/cart?sessionId='.$sessionId);

        $response->assertOk()->assertJsonCount(1, 'data.items');
    }

    public function test_authenticated_users_cart_is_tied_to_the_user_not_the_session(): void
    {
        $variant = $this->makeVariant(100, 5);
        $user = User::factory()->create();
        $headers = $this->authHeader($user);

        $this->withHeaders($headers)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1])
            ->assertOk();

        // A different guest session must not see the authenticated user's items. Headers set
        // via withHeaders() persist across requests within a test, so the Authorization header
        // from the request above must be flushed first. Passport's TokenGuard also caches the
        // resolved user on the guard instance for the lifetime of the test (see the identical
        // note in AuthControllerTest::test_logout_revokes_token) — forgetGuards() is required
        // too, or the guard would keep reporting the previously-resolved user regardless of
        // the header change.
        $this->flushHeaders();
        Auth::shouldUse('web');
        Auth::forgetGuards();
        $this->withHeader('X-Cart-Session-ID', 'unrelated-session')
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.items', []);

        // ...and the same user, even sending an unrelated session header, still sees their cart.
        Auth::forgetGuards();
        $this->withHeaders($headers)
            ->withHeader('X-Cart-Session-ID', 'unrelated-session')
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonCount(1, 'data.items');
    }

    public function test_active_sitewide_promotion_is_reflected_in_cart_totals(): void
    {
        $variant = $this->makeVariant(200, 5);
        Promotion::create([
            'name' => 'Sitewide 10%',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-Cart-Session-ID', 'session-'.uniqid())
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1]);

        $response->assertOk()
            ->assertJsonPath('data.total', 200)
            ->assertJsonPath('data.promotionDiscount', 20)
            ->assertJsonPath('data.discountedTotal', 180);
    }

    public function test_update_item_changes_the_quantity(): void
    {
        $variant = $this->makeVariant(100, 10);
        $session = 'session-'.uniqid();

        $addResponse = $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1]);
        $itemId = $addResponse->json('data.items.0.id');

        $response = $this->withHeader('X-Cart-Session-ID', $session)
            ->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 4]);

        $response->assertOk()->assertJsonPath('data.items.0.quantity', 4);
    }

    public function test_update_item_clamps_quantity_to_available_stock(): void
    {
        $variant = $this->makeVariant(100, 5);
        $session = 'session-'.uniqid();

        $addResponse = $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1]);
        $itemId = $addResponse->json('data.items.0.id');

        $response = $this->withHeader('X-Cart-Session-ID', $session)
            ->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 999]);

        $response->assertOk()->assertJsonPath('data.items.0.quantity', 5);
    }

    public function test_update_item_returns_404_for_an_item_that_does_not_belong_to_the_session(): void
    {
        $variant = $this->makeVariant(100, 5);
        $owner = 'session-owner-'.uniqid();

        $addResponse = $this->withHeader('X-Cart-Session-ID', $owner)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1]);
        $itemId = $addResponse->json('data.items.0.id');

        $response = $this->withHeader('X-Cart-Session-ID', 'session-intruder-'.uniqid())
            ->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 2]);

        $response->assertStatus(404);
    }

    public function test_remove_item_deletes_it_from_the_cart(): void
    {
        $variant = $this->makeVariant(100, 5);
        $session = 'session-'.uniqid();

        $addResponse = $this->withHeader('X-Cart-Session-ID', $session)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 1]);
        $itemId = $addResponse->json('data.items.0.id');

        $response = $this->withHeader('X-Cart-Session-ID', $session)
            ->deleteJson("/api/v1/cart/items/{$itemId}");

        $response->assertOk()->assertJsonPath('data.items', []);
        $this->assertDatabaseMissing('cart_items', ['id' => $itemId]);
    }

    public function test_merge_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/cart/merge', ['sessionId' => 'session-'.uniqid()]);

        $response->assertUnauthorized();
    }

    public function test_merge_moves_guest_cart_items_into_the_authenticated_users_cart(): void
    {
        $variant = $this->makeVariant(100, 5);
        $guestSession = 'guest-session-'.uniqid();

        $this->withHeader('X-Cart-Session-ID', $guestSession)
            ->postJson('/api/v1/cart', ['variantId' => $variant->id, 'quantity' => 2])
            ->assertOk();

        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/v1/cart/merge', ['sessionId' => $guestSession]);

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseMissing('carts', ['session_id' => $guestSession]);
    }
}
