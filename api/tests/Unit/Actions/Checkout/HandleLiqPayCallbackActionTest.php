<?php

namespace Tests\Unit\Actions\Checkout;

use App\Api\V1\Actions\Checkout\HandleLiqPayCallbackAction;
use App\Models\Order;
use App\Services\Payment\LiqPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class HandleLiqPayCallbackActionTest extends TestCase
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

    private function mockLiqPay(array $payload): void
    {
        $this->mock(LiqPayService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('verifySignature')->once()->andReturn(true);
            $mock->shouldReceive('decodeCallbackData')->once()->andReturn($payload);
        });
    }

    public function test_execute_logs_and_returns_early_when_signature_is_invalid(): void
    {
        Log::spy();
        $this->mock(LiqPayService::class, function ($mock) {
            $mock->shouldReceive('verifySignature')->once()->andReturn(false);
            $mock->shouldNotReceive('decodeCallbackData');
        });

        $order = $this->makeOrder();

        app(HandleLiqPayCallbackAction::class)->execute('data', 'bad-signature');

        Log::shouldHaveReceived('warning')->once()->with('LiqPay callback: invalid signature');
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_execute_logs_and_returns_early_when_order_id_is_missing(): void
    {
        Log::spy();
        $this->mockLiqPay(['status' => 'success']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        Log::shouldHaveReceived('warning')->once()->with(
            'LiqPay callback: missing order_id or status',
            ['payload' => ['status' => 'success']]
        );
    }

    public function test_execute_logs_and_returns_early_when_status_is_missing(): void
    {
        Log::spy();
        $this->mockLiqPay(['order_id' => 'FKX-20260101-AAAAAA']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        Log::shouldHaveReceived('warning')->once()->with(
            'LiqPay callback: missing order_id or status',
            ['payload' => ['order_id' => 'FKX-20260101-AAAAAA']]
        );
    }

    public function test_execute_logs_and_returns_early_when_order_is_not_found(): void
    {
        Log::spy();
        $this->mockLiqPay(['order_id' => 'FKX-DOES-NOT-EXIST', 'status' => 'success']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        Log::shouldHaveReceived('warning')->once()->with(
            'LiqPay callback: order not found',
            ['order_number' => 'FKX-DOES-NOT-EXIST']
        );
    }

    public function test_execute_is_a_no_op_when_the_order_is_already_paid(): void
    {
        $order = $this->makeOrder(['payment_status' => 'paid', 'status' => 'completed']);
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'success', 'payment_id' => 'pay_1']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('completed', $fresh->status);
        $this->assertNull($fresh->payment_reference);
    }

    public function test_execute_marks_order_paid_on_success_status(): void
    {
        $order = $this->makeOrder();
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'success', 'payment_id' => 'pay_42']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('processing', $fresh->status);
        $this->assertSame('pay_42', $fresh->payment_reference);
        $this->assertNotNull($fresh->paid_at);
    }

    public function test_execute_marks_order_paid_on_sandbox_status(): void
    {
        $order = $this->makeOrder();
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'sandbox']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('processing', $fresh->status);
        $this->assertNull($fresh->payment_reference);
    }

    public function test_execute_marks_order_failed_on_failure_status(): void
    {
        $order = $this->makeOrder();
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'failure', 'payment_id' => 'pay_7']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $fresh = $order->fresh();
        $this->assertSame('failed', $fresh->payment_status);
        $this->assertSame('pay_7', $fresh->payment_reference);
        // Order status (fulfilment state) is untouched by a failed payment.
        $this->assertSame('pending_payment', $fresh->status);
    }

    public function test_execute_marks_order_failed_on_error_status(): void
    {
        $order = $this->makeOrder();
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'error']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $this->assertSame('failed', $order->fresh()->payment_status);
    }

    public function test_execute_marks_order_refunded_on_reversed_status(): void
    {
        // payment_status starts as 'processing' (not 'paid') so the already-paid guard
        // doesn't short-circuit the refund transition.
        $order = $this->makeOrder(['payment_status' => 'processing', 'status' => 'processing']);
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'reversed']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $fresh = $order->fresh();
        $this->assertSame('refunded', $fresh->payment_status);
        $this->assertSame('refunded', $fresh->status);
    }

    public function test_execute_does_nothing_for_an_intermediate_status(): void
    {
        $order = $this->makeOrder();
        $this->mockLiqPay(['order_id' => $order->order_number, 'status' => 'wait_accept']);

        app(HandleLiqPayCallbackAction::class)->execute('data', 'sig');

        $fresh = $order->fresh();
        $this->assertSame('pending', $fresh->payment_status);
        $this->assertSame('pending_payment', $fresh->status);
    }
}
