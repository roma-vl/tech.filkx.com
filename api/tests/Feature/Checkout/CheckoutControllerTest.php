<?php

namespace Tests\Feature\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price, int $stock, string $status = 'active', int $reserved = 0): ProductVariant
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

    private function makeCartWithItem(ProductVariant $variant, int $quantity, ?string $sessionId = null, ?User $user = null): Cart
    {
        $cart = Cart::create([
            'session_id' => $user ? null : ($sessionId ?? 'session-'.uniqid()),
            'user_id' => $user?->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    // --- quickOrder ---

    public function test_quick_order_creates_an_order_and_reserves_stock(): void
    {
        $variant = $this->makeVariant(150, 5);

        $response = $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван Петренко',
            'customerPhone' => '+380501112233',
            'variantId' => $variant->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.paymentMethod', 'cod')
            ->assertJsonPath('data.totalPrice', 150)
            ->assertJsonCount(1, 'data.items');

        $this->assertDatabaseHas('stocks', ['variant_id' => $variant->id, 'reserved' => 1]);
        $this->assertDatabaseHas('orders', [
            'order_number' => $response->json('data.orderNumber'),
            'customer_phone' => '+380501112233',
        ]);
    }

    public function test_quick_order_rejects_an_out_of_stock_variant(): void
    {
        $variant = $this->makeVariant(150, 1, reserved: 1);

        $response = $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван Петренко',
            'customerPhone' => '+380501112233',
            'variantId' => $variant->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('orders', ['customer_phone' => '+380501112233']);
    }

    public function test_quick_order_rejects_an_inactive_product(): void
    {
        $variant = $this->makeVariant(150, 5, status: 'inactive');

        $response = $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван Петренко',
            'customerPhone' => '+380501112233',
            'variantId' => $variant->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_quick_order_defaults_payment_method_to_cod(): void
    {
        $variant = $this->makeVariant(150, 5);

        $response = $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван',
            'customerPhone' => '+380501112233',
            'variantId' => $variant->id,
        ]);

        $response->assertCreated()->assertJsonPath('data.paymentMethod', 'cod');
    }

    public function test_quick_order_accepts_card_as_a_payment_method(): void
    {
        $variant = $this->makeVariant(150, 5);

        $response = $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван',
            'customerPhone' => '+380501112233',
            'variantId' => $variant->id,
            'paymentMethod' => 'card',
        ]);

        $response->assertCreated()->assertJsonPath('data.paymentMethod', 'card');
    }

    public function test_quick_order_rejects_an_unsupported_payment_method(): void
    {
        $variant = $this->makeVariant(150, 5);

        $response = $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван',
            'customerPhone' => '+380501112233',
            'variantId' => $variant->id,
            'paymentMethod' => 'crypto',
        ]);

        $response->assertStatus(422);
    }

    public function test_quick_order_requires_customer_name_and_phone(): void
    {
        $variant = $this->makeVariant(150, 5);

        $this->postJson('/api/v1/checkout/quick', ['variantId' => $variant->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customerName', 'customerPhone']);
    }

    public function test_quick_order_rejects_an_unknown_variant(): void
    {
        $this->postJson('/api/v1/checkout/quick', [
            'customerName' => 'Іван',
            'customerPhone' => '+380501112233',
            'variantId' => 999999,
        ])->assertStatus(422)->assertJsonValidationErrors(['variantId']);
    }

    // --- placeOrder (main cart checkout) ---

    private function validOrderPayload(array $overrides = []): array
    {
        return array_merge([
            'customerName' => 'Іван Петренко',
            'customerPhone' => '+380501112233',
            'customerEmail' => 'ivan@example.com',
            'shippingCountry' => 'Ukraine',
            'shippingCity' => 'Київ',
            'shippingAddress' => 'вул. Хрещатик, 1',
            'deliveryMethod' => 'nova_poshta',
            'paymentMethod' => 'cod',
        ], $overrides);
    }

    public function test_place_order_creates_an_order_from_the_real_cart_and_clears_it(): void
    {
        $variant = $this->makeVariant(300, 5);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 2, $sessionId);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.totalPrice', 600)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseHas('stocks', ['variant_id' => $variant->id, 'reserved' => 2]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_place_order_rejects_an_empty_cart(): void
    {
        $sessionId = 'session-'.uniqid();
        Cart::create(['session_id' => $sessionId]);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload());

        $response->assertStatus(422)->assertJsonPath('status', 'error');
    }

    public function test_place_order_rejects_when_there_is_no_cart_for_the_session(): void
    {
        $response = $this->withHeader('X-Cart-Session-ID', 'session-'.uniqid())
            ->postJson('/api/v1/checkout', $this->validOrderPayload());

        $response->assertStatus(422);
    }

    public function test_place_order_rejects_when_stock_is_insufficient(): void
    {
        $variant = $this->makeVariant(300, 1);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 5, $sessionId);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload());

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
        // Cart must survive a failed checkout so the customer doesn't lose their items.
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_place_order_rejects_when_the_product_is_inactive(): void
    {
        $variant = $this->makeVariant(300, 5, status: 'inactive');
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload());

        $response->assertStatus(422);
    }

    public function test_place_order_requires_required_fields(): void
    {
        $variant = $this->makeVariant(300, 5);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload(['customerEmail' => '']));

        $response->assertStatus(422)->assertJsonValidationErrors(['customerEmail']);
    }

    public function test_place_order_applies_a_valid_coupon_discount(): void
    {
        $variant = $this->makeVariant(200, 5);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'amount' => 10, 'is_active' => true]);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload(['couponCode' => 'save10']));

        $response->assertCreated()
            ->assertJsonPath('data.couponCode', 'SAVE10')
            ->assertJsonPath('data.discountAmount', 20)
            ->assertJsonPath('data.totalPrice', 180);

        $this->assertDatabaseHas('coupons', ['code' => 'SAVE10', 'used_count' => 1]);
    }

    public function test_place_order_rejects_an_invalid_coupon_code(): void
    {
        $variant = $this->makeVariant(200, 5);
        $sessionId = 'session-'.uniqid();
        $this->makeCartWithItem($variant, 1, $sessionId);

        $response = $this->withHeader('X-Cart-Session-ID', $sessionId)
            ->postJson('/api/v1/checkout', $this->validOrderPayload(['couponCode' => 'DOESNOTEXIST']));

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_place_order_for_an_authenticated_user_uses_their_cart_and_links_the_order(): void
    {
        $variant = $this->makeVariant(300, 5);
        $user = User::factory()->create();
        $this->makeCartWithItem($variant, 1, null, $user);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson('/api/v1/checkout', $this->validOrderPayload());

        $response->assertCreated();
        $this->assertDatabaseHas('orders', [
            'order_number' => $response->json('data.orderNumber'),
            'user_id' => $user->id,
        ]);
    }
}
