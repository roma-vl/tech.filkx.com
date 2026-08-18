<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\CartRepository;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CartRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CartRepository::class);
    }

    private function makeVariant(): ProductVariant
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => 100,
        ]);
    }

    public function test_find_or_create_for_user_creates_a_cart_when_none_exists(): void
    {
        $user = User::factory()->create();

        $cart = $this->repository->findOrCreateForUser($user->id);

        $this->assertSame($user->id, $cart->user_id);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'user_id' => $user->id]);
    }

    public function test_find_or_create_for_user_returns_the_existing_cart(): void
    {
        $user = User::factory()->create();
        $existing = Cart::create(['user_id' => $user->id]);

        $cart = $this->repository->findOrCreateForUser($user->id);

        $this->assertSame($existing->id, $cart->id);
        $this->assertSame(1, Cart::where('user_id', $user->id)->count());
    }

    public function test_find_or_create_for_session_creates_a_cart_when_none_exists(): void
    {
        $sessionId = 'session-'.uniqid();

        $cart = $this->repository->findOrCreateForSession($sessionId);

        $this->assertSame($sessionId, $cart->session_id);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'session_id' => $sessionId]);
    }

    public function test_find_or_create_for_session_returns_the_existing_cart(): void
    {
        $sessionId = 'session-'.uniqid();
        $existing = Cart::create(['session_id' => $sessionId]);

        $cart = $this->repository->findOrCreateForSession($sessionId);

        $this->assertSame($existing->id, $cart->id);
        $this->assertSame(1, Cart::where('session_id', $sessionId)->count());
    }

    public function test_find_by_session_id_returns_the_matching_cart(): void
    {
        $sessionId = 'session-'.uniqid();
        $cart = Cart::create(['session_id' => $sessionId]);

        $found = $this->repository->findBySessionId($sessionId);

        $this->assertNotNull($found);
        $this->assertSame($cart->id, $found->id);
    }

    public function test_find_by_session_id_returns_null_when_no_cart_matches(): void
    {
        $this->assertNull($this->repository->findBySessionId('does-not-exist'));
    }

    public function test_find_item_returns_the_matching_item_for_that_cart(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);
        $item = CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 2]);

        $found = $this->repository->findItem($cart, $item->id);

        $this->assertNotNull($found);
        $this->assertSame($item->id, $found->id);
    }

    public function test_find_item_returns_null_when_the_item_belongs_to_a_different_cart(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);
        $otherCart = Cart::create(['session_id' => 'session-'.uniqid()]);
        $item = CartItem::create(['cart_id' => $otherCart->id, 'variant_id' => $variant->id, 'quantity' => 2]);

        $found = $this->repository->findItem($cart, $item->id);

        $this->assertNull($found);
    }

    public function test_find_item_by_variant_returns_the_matching_item(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);
        $item = CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 2]);

        $found = $this->repository->findItemByVariant($cart, $variant->id);

        $this->assertNotNull($found);
        $this->assertSame($item->id, $found->id);
    }

    public function test_find_item_by_variant_returns_null_when_the_cart_has_no_such_variant(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);

        $found = $this->repository->findItemByVariant($cart, $variant->id);

        $this->assertNull($found);
    }

    public function test_add_item_creates_a_cart_item(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);

        $item = $this->repository->addItem($cart, $variant->id, 3);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_update_item_quantity_updates_the_item(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);
        $item = CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 1]);

        $result = $this->repository->updateItemQuantity($item, 5);

        $this->assertTrue($result);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 5]);
    }

    public function test_remove_item_deletes_the_item_and_returns_true(): void
    {
        $variant = $this->makeVariant();
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);
        $item = CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 1]);

        $result = $this->repository->removeItem($item);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_merge_carts_combines_quantities_for_shared_variants(): void
    {
        $variant = $this->makeVariant();
        $userCart = Cart::create(['session_id' => 'user-session-'.uniqid()]);
        CartItem::create(['cart_id' => $userCart->id, 'variant_id' => $variant->id, 'quantity' => 2]);
        $anonCart = Cart::create(['session_id' => 'anon-session-'.uniqid()]);
        $anonItem = CartItem::create(['cart_id' => $anonCart->id, 'variant_id' => $variant->id, 'quantity' => 3]);

        $this->repository->mergeCarts($userCart, $anonCart);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseMissing('cart_items', ['id' => $anonItem->id]);
    }

    public function test_merge_carts_moves_items_with_variants_not_already_in_the_user_cart(): void
    {
        $variant = $this->makeVariant();
        $userCart = Cart::create(['session_id' => 'user-session-'.uniqid()]);
        $anonCart = Cart::create(['session_id' => 'anon-session-'.uniqid()]);
        $anonItem = CartItem::create(['cart_id' => $anonCart->id, 'variant_id' => $variant->id, 'quantity' => 3]);

        $this->repository->mergeCarts($userCart, $anonCart);

        $this->assertDatabaseHas('cart_items', [
            'id' => $anonItem->id,
            'cart_id' => $userCart->id,
            'quantity' => 3,
        ]);
    }

    public function test_merge_carts_deletes_the_anonymous_cart(): void
    {
        $userCart = Cart::create(['session_id' => 'user-session-'.uniqid()]);
        $anonCart = Cart::create(['session_id' => 'anon-session-'.uniqid()]);

        $this->repository->mergeCarts($userCart, $anonCart);

        $this->assertDatabaseMissing('carts', ['id' => $anonCart->id]);
    }
}
