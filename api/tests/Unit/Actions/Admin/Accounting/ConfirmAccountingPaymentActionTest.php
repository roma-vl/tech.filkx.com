<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\ConfirmAccountingPaymentAction;
use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Tests\TestCase;

class ConfirmAccountingPaymentActionTest extends TestCase
{
    private function makeOrder(): Order
    {
        return new Order([
            'order_number' => 'FKX-1',
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'status' => 'pending',
            'payment_status' => 'pending',
            'total_price' => 100,
        ]);
    }

    public function test_execute_marks_the_order_paid_and_completed_when_approved(): void
    {
        $order = $this->makeOrder();

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($order) {
            $mock->shouldReceive('find')->once()->with(1)->andReturn($order);
            $mock->shouldReceive('update')->once()->with($order, [
                'payment_status' => 'paid',
                'status' => 'completed',
            ])->andReturn($order);
        });

        $result = app(ConfirmAccountingPaymentAction::class)->execute(1, true);

        $this->assertSame($order, $result);
    }

    public function test_execute_marks_the_order_failed_and_cancelled_when_rejected(): void
    {
        $order = $this->makeOrder();

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($order) {
            $mock->shouldReceive('find')->once()->with(1)->andReturn($order);
            $mock->shouldReceive('update')->once()->with($order, [
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ])->andReturn($order);
        });

        $result = app(ConfirmAccountingPaymentAction::class)->execute(1, false);

        $this->assertSame($order, $result);
    }

    public function test_execute_throws_when_the_order_does_not_exist(): void
    {
        $this->mock(OrderRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
            $mock->shouldNotReceive('update');
        });

        $this->expectException(OrderNotFoundException::class);

        app(ConfirmAccountingPaymentAction::class)->execute(999, true);
    }
}
