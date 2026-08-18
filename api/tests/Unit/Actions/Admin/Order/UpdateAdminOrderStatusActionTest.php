<?php

namespace Tests\Unit\Actions\Admin\Order;

use App\Api\Admin\Actions\Order\UpdateAdminOrderStatusAction;
use App\Api\Admin\Dto\UpdateOrderStatusDto;
use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAdminOrderStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAdminOrderStatusAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateAdminOrderStatusAction::class);
    }

    private function makeOrderWithStock(string $status, ?int $variantId, int $quantity = 1, int $stockQuantity = 10, int $reserved = 0): array
    {
        $order = Order::create([
            'order_number' => 'FKX-'.uniqid(),
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'status' => $status,
            'payment_status' => 'pending',
            'total_price' => 100,
            'discount_amount' => 0,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variantId,
            'product_name' => 'Товар',
            'sku' => 'sku-'.uniqid(),
            'price' => 100,
            'quantity' => $quantity,
        ]);

        $stock = null;
        if ($variantId) {
            $warehouse = Warehouse::create(['name' => 'Main']);
            $stock = Stock::create([
                'variant_id' => $variantId,
                'warehouse_id' => $warehouse->id,
                'quantity' => $stockQuantity,
                'reserved' => $reserved,
            ]);
        }

        return [$order, $stock];
    }

    private function makeVariant(): ProductVariant
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => 100,
        ]);
    }

    public function test_execute_throws_not_found_for_an_unknown_order(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $this->action->execute(999999, new UpdateOrderStatusDto('paid', null, null));
    }

    public function test_execute_skips_stock_adjustment_for_items_without_a_variant(): void
    {
        [$order] = $this->makeOrderWithStock('pending_payment', null);

        $result = $this->action->execute($order->id, new UpdateOrderStatusDto('paid', null, null));

        $this->assertSame('paid', $result->status);
        $this->assertSame('paid', $result->payment_status);
    }

    public function test_execute_re_deducts_stock_when_moving_from_cancelled_back_to_paid(): void
    {
        $variant = $this->makeVariant();
        [$order, $stock] = $this->makeOrderWithStock('cancelled', $variant->id, quantity: 3, stockQuantity: 20, reserved: 0);

        $this->action->execute($order->id, new UpdateOrderStatusDto('paid', null, null));

        $stock->refresh();
        $this->assertSame(17, $stock->quantity);
    }

    public function test_execute_sets_payment_status_to_refunded_when_status_becomes_refunded(): void
    {
        $variant = $this->makeVariant();
        [$order] = $this->makeOrderWithStock('paid', $variant->id, quantity: 1, stockQuantity: 10, reserved: 0);

        $result = $this->action->execute($order->id, new UpdateOrderStatusDto('refunded', null, null));

        $this->assertSame('refunded', $result->status);
        $this->assertSame('refunded', $result->payment_status);
    }
}
