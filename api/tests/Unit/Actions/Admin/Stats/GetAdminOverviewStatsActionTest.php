<?php

namespace Tests\Unit\Actions\Admin\Stats;

use App\Api\Admin\Actions\Stats\GetAdminOverviewStatsAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAdminOverviewStatsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetAdminOverviewStatsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetAdminOverviewStatsAction::class);
    }

    private function makeOrder(string $status, float $totalPrice): Order
    {
        return Order::create([
            'order_number' => 'FKX-'.uniqid(),
            'customer_name' => 'Іван',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => $status,
            'total_price' => $totalPrice,
            'discount_amount' => 0,
        ]);
    }

    public function test_execute_returns_the_four_overview_cards_in_order(): void
    {
        $result = $this->action->execute();

        $this->assertCount(4, $result['overview']);
        $this->assertSame('Total Customers', $result['overview'][0]['label']);
        $this->assertSame('Orders Completed', $result['overview'][1]['label']);
        $this->assertSame('Total Revenue', $result['overview'][2]['label']);
        $this->assertSame('Products Active', $result['overview'][3]['label']);
    }

    public function test_execute_counts_users_orders_and_products(): void
    {
        $userBaseline = User::count();
        $orderBaseline = Order::count();
        $productBaseline = Product::count();

        User::factory()->count(2)->create();
        $this->makeOrder('completed', 100);
        $this->makeOrder('pending', 50);
        Product::create([
            'slug' => 'zz-test-product',
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $result = $this->action->execute();

        $this->assertSame(number_format($userBaseline + 2), $result['overview'][0]['value']);
        $this->assertSame(number_format($orderBaseline + 2), $result['overview'][1]['value']);
        $this->assertSame(number_format($productBaseline + 1), $result['overview'][3]['value']);
    }

    public function test_execute_only_sums_the_revenue_of_completed_orders(): void
    {
        $this->makeOrder('completed', 100);
        $this->makeOrder('pending', 999);

        $result = $this->action->execute();

        $this->assertSame('₴'.number_format(100, 2), $result['overview'][2]['value']);
    }
}
