<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payment\LiqPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'FKX-'.strtoupper(Str::random(10)),
            'customer_name' => 'Іван Петренко',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'status' => 'pending_payment',
            'total_price' => 500,
        ], $overrides));
    }

    // --- initiateLiqPay ---

    public function test_initiate_liqpay_returns_404_when_the_order_does_not_exist(): void
    {
        $this->postJson('/api/v1/payments/orders/FKX-DOES-NOT-EXIST/liqpay')
            ->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_initiate_liqpay_returns_the_checkout_payload_on_success(): void
    {
        $order = $this->makeOrder();
        $expectedPayload = ['data' => 'encoded', 'signature' => 'signed', 'checkoutUrl' => LiqPayService::CHECKOUT_URL];

        $this->mock(LiqPayService::class, function ($mock) use ($expectedPayload) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('buildCheckoutPayload')->andReturn($expectedPayload);
        });

        $response = $this->postJson("/api/v1/payments/orders/{$order->order_number}/liqpay");

        $response->assertOk()
            ->assertJsonPath('data.data', 'encoded')
            ->assertJsonPath('data.signature', 'signed')
            ->assertJsonPath('data.checkoutUrl', LiqPayService::CHECKOUT_URL);
    }

    public function test_initiate_liqpay_returns_422_when_liqpay_is_not_configured(): void
    {
        $order = $this->makeOrder();

        $this->mock(LiqPayService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(false);
        });

        $this->postJson("/api/v1/payments/orders/{$order->order_number}/liqpay")
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_initiate_liqpay_returns_422_when_the_order_is_already_paid(): void
    {
        $order = $this->makeOrder(['payment_status' => 'paid']);

        $this->mock(LiqPayService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
        });

        $this->postJson("/api/v1/payments/orders/{$order->order_number}/liqpay")
            ->assertStatus(422);
    }

    // --- liqPayCallback ---

    public function test_liqpay_callback_marks_the_order_paid_and_always_returns_ok(): void
    {
        $order = $this->makeOrder();

        $this->mock(LiqPayService::class, function ($mock) use ($order) {
            $mock->shouldReceive('verifySignature')->andReturn(true);
            $mock->shouldReceive('decodeCallbackData')->andReturn([
                'order_id' => $order->order_number,
                'status' => 'success',
                'payment_id' => 'pay_1',
            ]);
        });

        $response = $this->postJson('/api/v1/payments/liqpay/callback', ['data' => 'x', 'signature' => 'y']);

        $response->assertOk()->assertSee('OK');
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_liqpay_callback_returns_ok_even_when_the_signature_is_invalid(): void
    {
        $order = $this->makeOrder();

        $this->mock(LiqPayService::class, function ($mock) {
            $mock->shouldReceive('verifySignature')->andReturn(false);
            $mock->shouldNotReceive('decodeCallbackData');
        });

        $response = $this->postJson('/api/v1/payments/liqpay/callback', ['data' => 'x', 'signature' => 'bad']);

        $response->assertOk()->assertSee('OK');
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    // --- orderStatus ---

    public function test_order_status_returns_404_when_the_order_does_not_exist(): void
    {
        $this->getJson('/api/v1/payments/orders/FKX-DOES-NOT-EXIST/status')
            ->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_order_status_returns_the_order_with_its_items(): void
    {
        $order = $this->makeOrder();
        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => null,
            'product_name' => 'Товар',
            'sku' => 'sku-1',
            'price' => 500,
            'quantity' => 1,
        ]);

        $response = $this->getJson("/api/v1/payments/orders/{$order->order_number}/status");

        $response->assertOk()
            ->assertJsonPath('data.orderNumber', $order->order_number)
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonCount(1, 'data.items');
    }
}
