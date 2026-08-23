<?php

namespace Tests\Unit\Actions\Checkout;

use App\Api\V1\Actions\Checkout\InitiateLiqPayPaymentAction;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Models\Order;
use App\Services\Payment\LiqPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class InitiateLiqPayPaymentActionTest extends TestCase
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

    public function test_execute_throws_when_liqpay_is_not_configured(): void
    {
        $this->mock(LiqPayService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $order = $this->makeOrder();

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Онлайн-оплата тимчасово недоступна. Оберіть інший спосіб оплати.');

        app(InitiateLiqPayPaymentAction::class)->execute($order);
    }

    public function test_execute_throws_when_the_order_is_already_paid(): void
    {
        $this->mock(LiqPayService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        });

        $order = $this->makeOrder(['payment_status' => 'paid']);

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Це замовлення вже оплачено');

        app(InitiateLiqPayPaymentAction::class)->execute($order);
    }

    public function test_execute_returns_the_liqpay_checkout_payload_on_the_happy_path(): void
    {
        config([
            'app.frontend_url' => 'https://filkx.com',
            'app.url' => 'https://api.filkx.com',
        ]);

        $order = $this->makeOrder(['order_number' => 'FKX-20260101-ABC123']);
        $expectedPayload = ['data' => 'encoded-data', 'signature' => 'signed', 'checkoutUrl' => LiqPayService::CHECKOUT_URL];

        $this->mock(LiqPayService::class, function ($mock) use ($order, $expectedPayload) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('buildCheckoutPayload')
                ->once()
                ->with(
                    Mockery::on(fn ($arg) => $arg->id === $order->id),
                    'https://filkx.com/cart?payment=liqpay&order=FKX-20260101-ABC123',
                    'https://api.filkx.com/api/v1/payments/liqpay/callback'
                )
                ->andReturn($expectedPayload);
        });

        $result = app(InitiateLiqPayPaymentAction::class)->execute($order);

        $this->assertSame($expectedPayload, $result);
    }
}
