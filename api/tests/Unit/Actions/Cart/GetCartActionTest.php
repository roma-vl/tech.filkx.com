<?php

namespace Tests\Unit\Actions\Cart;

use App\Api\V1\Actions\Cart\GetCartAction;
use App\Api\V1\Dto\CartSessionDto;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCartActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCartItemWithImages(array $images): CartItem
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => 100,
            'dimensions' => ['images' => $images],
        ]);

        $warehouse = Warehouse::create(['name' => 'Main']);
        Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'reserved' => 0,
        ]);

        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);

        return CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 1]);
    }

    public function test_execute_uses_the_image_marked_as_primary(): void
    {
        $item = $this->makeCartItemWithImages([
            ['url' => 'https://example.com/first.jpg', 'isPrimary' => false],
            ['url' => 'https://example.com/primary.jpg', 'isPrimary' => true],
        ]);

        $result = app(GetCartAction::class)->execute(
            new CartSessionDto(userId: null, sessionId: $item->cart->session_id)
        );

        $this->assertSame('https://example.com/primary.jpg', $result->items[0]['image']);
    }

    public function test_execute_falls_back_to_the_first_image_when_none_is_marked_primary(): void
    {
        $item = $this->makeCartItemWithImages([
            ['url' => 'https://example.com/first.jpg'],
            ['url' => 'https://example.com/second.jpg'],
        ]);

        $result = app(GetCartAction::class)->execute(
            new CartSessionDto(userId: null, sessionId: $item->cart->session_id)
        );

        $this->assertSame('https://example.com/first.jpg', $result->items[0]['image']);
    }

    public function test_resolve_cart_generates_a_random_session_id_for_an_anonymous_request(): void
    {
        $cart = app(GetCartAction::class)->resolveCart(new CartSessionDto(userId: null, sessionId: null));

        $this->assertNotNull($cart->session_id);
        $this->assertStringStartsWith('anon_', $cart->session_id);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'session_id' => $cart->session_id]);
    }
}
