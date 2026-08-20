<?php

namespace Tests\Unit\Actions\Cart;

use App\Api\V1\Actions\Cart\RemoveCartItemAction;
use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Exceptions\CartItemNotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveCartItemActionTest extends TestCase
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

    public function test_execute_removes_the_item_from_the_cart(): void
    {
        $variant = $this->makeVariant();
        $sessionId = 'session-'.uniqid();
        $cart = Cart::create(['session_id' => $sessionId]);
        $item = CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 1]);

        app(RemoveCartItemAction::class)->execute(
            new CartSessionDto(userId: null, sessionId: $sessionId),
            $item->id
        );

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_execute_throws_when_the_item_does_not_exist_in_the_cart(): void
    {
        $sessionId = 'session-'.uniqid();
        Cart::create(['session_id' => $sessionId]);

        $this->expectException(CartItemNotFoundException::class);

        app(RemoveCartItemAction::class)->execute(
            new CartSessionDto(userId: null, sessionId: $sessionId),
            999999
        );
    }
}
