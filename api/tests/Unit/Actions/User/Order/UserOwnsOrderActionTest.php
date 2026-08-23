<?php

namespace Tests\Unit\Actions\User\Order;

use App\Api\V1\Actions\User\Order\UserOwnsOrderAction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserOwnsOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private UserOwnsOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UserOwnsOrderAction::class);
    }

    private function makeOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'FKX-'.uniqid(),
            'customer_name' => 'Іван',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'pending',
            'total_price' => 100,
            'discount_amount' => 0,
        ], $attributes));
    }

    public function test_execute_returns_true_when_order_belongs_to_the_user_account(): void
    {
        $user = User::factory()->create(['email' => 'someone@example.com']);
        $order = $this->makeOrder(['user_id' => $user->id, 'customer_email' => 'other@example.com']);

        $this->assertTrue($this->action->execute($user, $order));
    }

    public function test_execute_returns_true_when_order_matches_the_users_email_for_a_guest_checkout(): void
    {
        $user = User::factory()->create(['email' => 'ivan@example.com']);
        $order = $this->makeOrder(['user_id' => null, 'customer_email' => 'ivan@example.com']);

        $this->assertTrue($this->action->execute($user, $order));
    }

    public function test_execute_returns_false_when_neither_account_nor_email_matches(): void
    {
        $user = User::factory()->create(['email' => 'someone@example.com']);
        $order = $this->makeOrder(['user_id' => null, 'customer_email' => 'other@example.com']);

        $this->assertFalse($this->action->execute($user, $order));
    }
}
