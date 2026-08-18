<?php

namespace Tests\Unit\Actions\Cart;

use App\Api\V1\Actions\Cart\AddToCartAction;
use App\Api\V1\Dto\AddToCartDto;
use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Exceptions\ProductVariantNotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddToCartActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price, int $stock, int $reserved = 0): ProductVariant
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
            'price' => $price,
        ]);

        $warehouse = Warehouse::create(['name' => 'Main']);
        Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $stock,
            'reserved' => $reserved,
        ]);

        return $variant;
    }

    private function sessionFor(string $sessionId): CartSessionDto
    {
        return new CartSessionDto(userId: null, sessionId: $sessionId);
    }

    public function test_execute_throws_when_neither_variant_nor_product_exist(): void
    {
        $this->expectException(ProductVariantNotFoundException::class);

        app(AddToCartAction::class)->execute(
            $this->sessionFor('session-'.uniqid()),
            new AddToCartDto(variantId: 999999, quantity: 1)
        );
    }

    public function test_execute_falls_back_to_the_products_first_variant_when_the_id_matches_a_product(): void
    {
        // Push the product id sequence ahead of the variant id sequence (one bare product with
        // no variant) so the target product's own id cannot collide with any real variant id -
        // otherwise ProductVariant::find() would resolve it directly and never fall through.
        Product::create([
            'slug' => 'filler-'.uniqid(),
            'name' => ['uk' => 'Filler', 'en' => 'Filler'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $variant = $this->makeVariant(100, 5);
        $sessionId = 'session-'.uniqid();

        app(AddToCartAction::class)->execute(
            $this->sessionFor($sessionId),
            new AddToCartDto(variantId: $variant->product_id, quantity: 2)
        );

        $cart = Cart::where('session_id', $sessionId)->first();
        $this->assertNotNull($cart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_execute_throws_when_the_variant_is_out_of_stock(): void
    {
        $variant = $this->makeVariant(100, 5, reserved: 5);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Товару немає в наявності');

        app(AddToCartAction::class)->execute(
            $this->sessionFor('session-'.uniqid()),
            new AddToCartDto(variantId: $variant->id, quantity: 1)
        );
    }

    public function test_execute_adds_a_new_item_when_none_exists_yet(): void
    {
        $variant = $this->makeVariant(100, 5);
        $sessionId = 'session-'.uniqid();

        app(AddToCartAction::class)->execute(
            $this->sessionFor($sessionId),
            new AddToCartDto(variantId: $variant->id, quantity: 3)
        );

        $cart = Cart::where('session_id', $sessionId)->first();
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_execute_clamps_a_new_items_quantity_to_available_stock(): void
    {
        $variant = $this->makeVariant(100, 3);
        $sessionId = 'session-'.uniqid();

        app(AddToCartAction::class)->execute(
            $this->sessionFor($sessionId),
            new AddToCartDto(variantId: $variant->id, quantity: 10)
        );

        $cart = Cart::where('session_id', $sessionId)->first();
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_execute_merges_quantity_into_an_existing_cart_item(): void
    {
        $variant = $this->makeVariant(100, 10);
        $sessionId = 'session-'.uniqid();
        $cart = Cart::create(['session_id' => $sessionId]);
        CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 2]);

        app(AddToCartAction::class)->execute(
            $this->sessionFor($sessionId),
            new AddToCartDto(variantId: $variant->id, quantity: 3)
        );

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);
    }

    public function test_execute_clamps_the_merged_quantity_to_available_stock(): void
    {
        $variant = $this->makeVariant(100, 5);
        $sessionId = 'session-'.uniqid();
        $cart = Cart::create(['session_id' => $sessionId]);
        CartItem::create(['cart_id' => $cart->id, 'variant_id' => $variant->id, 'quantity' => 3]);

        app(AddToCartAction::class)->execute(
            $this->sessionFor($sessionId),
            new AddToCartDto(variantId: $variant->id, quantity: 10)
        );

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);
    }
}
