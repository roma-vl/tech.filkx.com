<?php

namespace Tests\Unit\Actions\User\Order;

use App\Api\V1\Actions\User\Order\CancelUserOrderAction;
use App\Api\V1\Exceptions\OrderAccessDeniedException;
use App\Api\V1\Exceptions\OrderAlreadyCancelledException;
use App\Api\V1\Exceptions\OrderNotCancellableException;
use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelUserOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CancelUserOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CancelUserOrderAction::class);
    }

    private function makeOrder(User $user, string $status, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'FKX-'.uniqid(),
            'user_id' => $user->id,
            'customer_name' => 'Іван',
            'customer_email' => $user->email,
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => $status,
            'total_price' => 100,
            'discount_amount' => 0,
        ], $attributes));
    }

    public function test_execute_throws_not_found_when_order_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->expectException(OrderNotFoundException::class);

        $this->action->execute($user, 999999);
    }

    public function test_execute_throws_access_denied_when_user_does_not_own_the_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner, 'pending');
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);

        $this->expectException(OrderAccessDeniedException::class);

        $this->action->execute($stranger, $order->id);
    }

    public function test_execute_throws_not_cancellable_when_order_already_shipped(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'shipped');

        $this->expectException(OrderNotCancellableException::class);

        $this->action->execute($user, $order->id);
    }

    public function test_execute_throws_already_cancelled_when_order_already_cancelled(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'cancelled');

        $this->expectException(OrderAlreadyCancelledException::class);

        $this->action->execute($user, $order->id);
    }

    public function test_execute_cancels_a_cancellable_order(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, 'pending_payment');

        $result = $this->action->execute($user, $order->id);

        $this->assertSame('cancelled', $result->status);
        $this->assertSame('cancelled', $order->fresh()->status);
    }
}
