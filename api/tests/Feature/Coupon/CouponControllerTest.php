<?php

namespace Tests\Feature\Coupon;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price, int $stock = 5): ProductVariant
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
            'reserved' => 0,
        ]);

        return $variant;
    }

    private function makeCartWithItem(ProductVariant $variant, int $quantity, string $sessionId): Cart
    {
        $cart = Cart::create(['session_id' => $sessionId]);

        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    public function test_validate_coupon_returns_the_computed_discount_for_a_valid_percent_coupon(): void
    {
        $variant = $this->makeVariant(200);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'amount' => 10, 'is_active' => true]);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/coupons/validate', ['code' => 'save10']);

        $response->assertOk()
            ->assertJsonPath('data.code', 'SAVE10')
            ->assertJsonPath('data.type', 'percent')
            ->assertJsonPath('data.discount', 20);
    }

    public function test_validate_coupon_rejects_an_unknown_code(): void
    {
        $variant = $this->makeVariant(200);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/coupons/validate', ['code' => 'DOESNOTEXIST']);

        $response->assertStatus(422)->assertJsonPath('status', 'error');
    }

    public function test_validate_coupon_rejects_an_expired_coupon(): void
    {
        $variant = $this->makeVariant(200);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/coupons/validate', ['code' => 'EXPIRED']);

        $response->assertStatus(422);
    }

    public function test_validate_coupon_rejects_when_the_cart_is_empty(): void
    {
        $sessionId = 'session-'.uniqid();
        Cart::create(['session_id' => $sessionId]);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'amount' => 10, 'is_active' => true]);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/coupons/validate', ['code' => 'SAVE10']);

        $response->assertStatus(422);
    }

    public function test_validate_coupon_requires_a_code(): void
    {
        $this->postJson('/api/v1/coupons/validate', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
