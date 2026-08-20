<?php

namespace Tests\Unit\Actions\Admin\Stats;

use App\Api\Admin\Actions\Stats\GetAdminDistributionStatsAction;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAdminDistributionStatsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetAdminDistributionStatsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetAdminDistributionStatsAction::class);
    }

    private function makeOrder(string $status): Order
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
            'total_price' => 100,
            'discount_amount' => 0,
        ]);
    }

    public function test_execute_returns_placeholder_category_distribution_when_no_categories_exist(): void
    {
        Category::query()->delete();

        $result = $this->action->execute();

        $this->assertSame(
            ['Смартфони', 'Ноутбуки', 'Аксесуари', 'Побутова техніка'],
            array_column($result['plans'], 'label')
        );
    }

    public function test_execute_labels_each_category_using_its_localized_name(): void
    {
        // Only the first 5 categories are considered; clear the seeded catalog so
        // the category created below is guaranteed to be among them.
        Category::query()->delete();

        $category = Category::create([
            'slug' => 'zz-test-phones',
            'name' => ['uk' => 'Телефони', 'en' => 'Phones'],
        ]);
        $product = Product::create([
            'slug' => 'zz-test-product',
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $product->categories()->attach($category->id);

        $result = $this->action->execute();

        $plan = collect($result['plans'])->firstWhere('label', 'Телефони');
        $this->assertNotNull($plan);
        $this->assertSame(1, $plan['value']);
    }

    public function test_execute_returns_placeholder_order_status_distribution_when_no_orders_exist(): void
    {
        $result = $this->action->execute();

        $this->assertSame(
            ['Completed', 'Pending', 'Processing', 'Cancelled'],
            array_column($result['content'], 'label')
        );
    }

    public function test_execute_counts_orders_by_status(): void
    {
        $this->makeOrder('completed');
        $this->makeOrder('completed');
        $this->makeOrder('pending');

        $result = $this->action->execute();

        $byLabel = collect($result['content'])->pluck('value', 'label');
        $this->assertSame(2, $byLabel['Completed']);
        $this->assertSame(1, $byLabel['Pending']);
        $this->assertSame(0, $byLabel['Processing']);
        $this->assertSame(0, $byLabel['Cancelled']);
    }
}
