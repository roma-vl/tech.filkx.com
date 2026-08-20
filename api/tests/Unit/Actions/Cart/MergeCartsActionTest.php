<?php

namespace Tests\Unit\Actions\Cart;

use App\Api\V1\Actions\Cart\MergeCartsAction;
use App\Api\V1\Dto\MergeCartDto;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeCartsActionTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_execute_throws_unauthorized_when_no_user_is_authenticated(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unauthorized');

        app(MergeCartsAction::class)->execute(new MergeCartDto(sessionId: 'session-'.uniqid()));
    }

    public function test_execute_throws_unauthorized_with_a_401_status_code(): void
    {
        try {
            app(MergeCartsAction::class)->execute(new MergeCartDto(sessionId: 'session-'.uniqid()));
            $this->fail('Expected a RuntimeException to be thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame(401, $e->getCode());
        }
    }

    public function test_execute_merges_the_anonymous_carts_items_into_the_users_cart(): void
    {
        $variant = $this->makeVariant();
        $sessionId = 'session-'.uniqid();
        $anonCart = Cart::create(['session_id' => $sessionId]);
        CartItem::create(['cart_id' => $anonCart->id, 'variant_id' => $variant->id, 'quantity' => 2]);

        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        app(MergeCartsAction::class)->execute(new MergeCartDto(sessionId: $sessionId));

        $userCart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseMissing('carts', ['id' => $anonCart->id]);
    }

    public function test_execute_does_nothing_when_no_anonymous_cart_exists_for_the_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        app(MergeCartsAction::class)->execute(new MergeCartDto(sessionId: 'session-'.uniqid()));

        $userCart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertSame(0, $userCart->items()->count());
    }

    public function test_execute_does_nothing_when_the_anonymous_cart_is_already_the_users_cart(): void
    {
        $user = User::factory()->create();
        $sessionId = 'session-'.uniqid();
        // A cart record that is simultaneously the user's cart and matches the session id -
        // findBySessionId() and findOrCreateForUser() resolve to the very same row.
        $cart = Cart::create(['user_id' => $user->id, 'session_id' => $sessionId]);

        $this->actingAs($user, 'api');

        app(MergeCartsAction::class)->execute(new MergeCartDto(sessionId: $sessionId));

        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
    }
}
