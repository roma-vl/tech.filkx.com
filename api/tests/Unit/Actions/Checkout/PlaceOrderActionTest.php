<?php

namespace Tests\Unit\Actions\Checkout;

use App\Api\V1\Actions\Checkout\PlaceOrderAction;
use App\Api\V1\Dto\PlaceOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PlaceOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price, int $stock, bool $withStock = true, int $reserved = 0): ProductVariant
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

        if ($withStock) {
            $warehouse = Warehouse::create(['name' => 'Main']);
            Stock::create([
                'variant_id' => $variant->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $stock,
                'reserved' => $reserved,
            ]);
        }

        return $variant;
    }

    private function makeCartWithItem(ProductVariant $variant, int $quantity): Cart
    {
        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);

        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return $cart;
    }

    private function dtoFor(Cart $cart, ?string $couponCode = null): PlaceOrderDto
    {
        return new PlaceOrderDto(
            customerName: 'Іван Петренко',
            customerPhone: '+380501112233',
            customerEmail: 'ivan@example.com',
            shippingCountry: 'Ukraine',
            shippingCity: 'Київ',
            shippingAddress: 'вул. Хрещатик, 1',
            deliveryMethod: 'nova_poshta',
            paymentMethod: 'cod',
            sessionId: $cart->session_id,
            couponCode: $couponCode
        );
    }

    public function test_execute_throws_when_no_stock_record_exists_for_the_variant(): void
    {
        $variant = $this->makeVariant(150, 0, withStock: false);
        $cart = $this->makeCartWithItem($variant, 1);

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage("Складські дані для {$variant->sku} відсутні");

        app(PlaceOrderAction::class)->execute($this->dtoFor($cart));
    }

    public function test_execute_throws_when_the_coupon_has_expired(): void
    {
        $variant = $this->makeVariant(200, 5);
        $cart = $this->makeCartWithItem($variant, 1);
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Термін дії купона закінчився');

        app(PlaceOrderAction::class)->execute($this->dtoFor($cart, 'EXPIRED'));
    }

    public function test_execute_throws_when_the_coupon_usage_limit_is_reached(): void
    {
        $variant = $this->makeVariant(200, 5);
        $cart = $this->makeCartWithItem($variant, 1);
        Coupon::create([
            'code' => 'MAXED',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
            'usage_limit' => 5,
            'used_count' => 5,
        ]);

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Купон вичерпав ліміт використання');

        app(PlaceOrderAction::class)->execute($this->dtoFor($cart, 'MAXED'));
    }

    public function test_execute_applies_a_fixed_amount_coupon_discount(): void
    {
        $variant = $this->makeVariant(200, 5);
        $cart = $this->makeCartWithItem($variant, 1);
        Coupon::create(['code' => 'FIXED20', 'type' => 'fixed', 'amount' => 20, 'is_active' => true]);

        $order = app(PlaceOrderAction::class)->execute($this->dtoFor($cart, 'FIXED20'));

        $this->assertSame(20.0, (float) $order->discount_amount);
        $this->assertSame(180.0, (float) $order->total_price);
    }

    public function test_execute_caps_the_discount_at_the_cart_total_when_it_would_otherwise_exceed_it(): void
    {
        $variant = $this->makeVariant(50, 5);
        $cart = $this->makeCartWithItem($variant, 1);
        Coupon::create(['code' => 'BIG100', 'type' => 'fixed', 'amount' => 100, 'is_active' => true]);

        $order = app(PlaceOrderAction::class)->execute($this->dtoFor($cart, 'BIG100'));

        $this->assertSame(50.0, (float) $order->discount_amount);
        $this->assertSame(0.0, (float) $order->total_price);
    }

    public function test_execute_sends_an_order_confirmed_notification_to_the_customer_email(): void
    {
        Notification::fake();
        $variant = $this->makeVariant(150, 5);
        $cart = $this->makeCartWithItem($variant, 1);

        $order = app(PlaceOrderAction::class)->execute($this->dtoFor($cart));

        Notification::assertSentTo(
            new AnonymousNotifiable,
            OrderConfirmedNotification::class,
            function (OrderConfirmedNotification $notification, array $channels, AnonymousNotifiable $notifiable) use ($order) {
                return $notification->order->is($order)
                    && $notifiable->routes['mail'] === 'ivan@example.com';
            }
        );
    }
}
