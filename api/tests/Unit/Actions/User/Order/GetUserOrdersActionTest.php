<?php

namespace Tests\Unit\Actions\User\Order;

use App\Api\V1\Actions\User\Order\GetUserOrdersAction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserOrdersActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserOrdersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserOrdersAction::class);
    }

    private function makeOrderWithItem(User $user, string $status, array $attributes = []): Order
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
            'price' => 100,
        ]);

        $order = Order::create(array_merge([
            'order_number' => 'FKX-'.uniqid(),
            'user_id' => $user->id,
            'customer_name' => 'Іван Петренко',
            'customer_email' => $user->email,
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'shipping_city' => 'Київ',
            'shipping_country' => 'Україна',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => $status,
            'total_price' => 100,
            'discount_amount' => 0,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => 'Товар',
            'sku' => $variant->sku,
            'price' => 100,
            'quantity' => 1,
        ]);

        return $order->load('items.variant.product');
    }

    public function test_execute_returns_orders_belonging_to_the_users_account(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrderWithItem($user, 'pending');

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame($order->order_number, $result->first()['id']);
        $this->assertSame($order->id, $result->first()['dbId']);
        $this->assertCount(1, $result->first()['items']);
    }

    public function test_execute_returns_orders_matching_the_users_email_for_guest_checkouts(): void
    {
        $user = User::factory()->create(['email' => 'ivan@example.com']);
        $this->makeOrderWithItem($user, 'pending', ['user_id' => null, 'customer_email' => 'ivan@example.com']);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);
    }

    public function test_execute_maps_shipped_status_to_ukrainian_label_and_partial_tracking(): void
    {
        $user = User::factory()->create();
        $this->makeOrderWithItem($user, 'shipped');

        $result = $this->action->execute($user);

        $this->assertSame('shipped', $result->first()['statusCode']);
        $this->assertSame('Відправлено - в дорозі', $result->first()['status']);
        $trackingSteps = $result->first()['trackingSteps'];
        $this->assertFalse(collect($trackingSteps)->last()['done']);
    }

    public function test_execute_marks_cancelled_orders_with_a_two_step_terminated_timeline(): void
    {
        $user = User::factory()->create();
        $this->makeOrderWithItem($user, 'cancelled');

        $result = $this->action->execute($user);

        $this->assertSame('cancelled', $result->first()['statusCode']);
        $this->assertCount(2, $result->first()['trackingSteps']);
        $this->assertTrue(collect($result->first()['trackingSteps'])->every(fn ($step) => $step['done']));
    }

    public function test_execute_orders_results_by_most_recent_first(): void
    {
        $user = User::factory()->create();
        $older = $this->makeOrderWithItem($user, 'pending');
        $older->created_at = now()->subDays(2);
        $older->save();
        $newer = $this->makeOrderWithItem($user, 'pending');

        $result = $this->action->execute($user);

        $this->assertSame($newer->id, $result->first()['dbId']);
        $this->assertSame($older->id, $result->last()['dbId']);
    }
}
