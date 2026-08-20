<?php

namespace Tests\Unit\Actions\Checkout;

use App\Api\V1\Actions\Checkout\PlaceQuickOrderAction;
use App\Api\V1\Dto\PlaceQuickOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceQuickOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price, string $status = 'active'): ProductVariant
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
        ]);
    }

    private function addStock(ProductVariant $variant, int $quantity, int $reserved = 0): Stock
    {
        $warehouse = Warehouse::create(['name' => 'Main']);

        return Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $quantity,
            'reserved' => $reserved,
        ]);
    }

    private function dtoFor(ProductVariant $variant, string $paymentMethod = 'cod'): PlaceQuickOrderDto
    {
        return new PlaceQuickOrderDto(
            customerName: 'Іван Петренко',
            customerPhone: '+380501112233',
            variantId: $variant->id,
            paymentMethod: $paymentMethod
        );
    }

    public function test_execute_throws_when_the_variant_does_not_exist(): void
    {
        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Товар недоступний');

        app(PlaceQuickOrderAction::class)->execute(new PlaceQuickOrderDto(
            customerName: 'Іван',
            customerPhone: '+380501112233',
            variantId: 999999,
            paymentMethod: 'cod'
        ));
    }

    public function test_execute_throws_when_the_product_is_inactive(): void
    {
        $variant = $this->makeVariant(150, status: 'inactive');
        $this->addStock($variant, 5);

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Товар недоступний');

        app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant));
    }

    public function test_execute_throws_when_there_is_no_stock_available(): void
    {
        $variant = $this->makeVariant(150);
        $this->addStock($variant, 1, reserved: 1);

        $this->expectException(CheckoutValidationException::class);
        $this->expectExceptionMessage('Недостатньо товару в наявності');

        app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant));
    }

    public function test_execute_does_not_require_a_stock_record_to_exist(): void
    {
        $variant = $this->makeVariant(150);

        $order = app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant));

        $this->assertSame('pending_payment', $order->status);
        $this->assertSame(150.0, (float) $order->total_price);
    }

    public function test_execute_reserves_one_unit_of_stock_on_success(): void
    {
        $variant = $this->makeVariant(150);
        $this->addStock($variant, 5);

        app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant));

        $this->assertDatabaseHas('stocks', ['variant_id' => $variant->id, 'reserved' => 1]);
    }

    public function test_execute_creates_an_order_with_a_single_quantity_one_item_and_fixed_shipping_placeholder(): void
    {
        $variant = $this->makeVariant(299.99);
        $this->addStock($variant, 5);

        $order = app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant, 'card'));

        $this->assertSame('Ukraine', $order->shipping_country);
        $this->assertSame('Київ', $order->shipping_city);
        $this->assertSame('nova_poshta', $order->delivery_method);
        $this->assertSame('card', $order->payment_method);
        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertSame(299.99, (float) $order->total_price);
        $this->assertCount(1, $order->items);
        $this->assertSame(1, $order->items->first()->quantity);
        $this->assertSame($variant->sku, $order->items->first()->sku);
        $this->assertMatchesRegularExpression('/^FKX-\d{8}-[A-Z0-9]{6}$/', $order->order_number);
    }

    public function test_execute_defaults_the_customer_email_and_leaves_the_order_guest_when_unauthenticated(): void
    {
        $variant = $this->makeVariant(150);
        $this->addStock($variant, 5);

        $order = app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant));

        $this->assertNull($order->user_id);
        $this->assertSame('quick-order@electro.com', $order->customer_email);
    }

    public function test_execute_links_the_order_and_uses_the_real_email_for_an_authenticated_user(): void
    {
        $variant = $this->makeVariant(150);
        $this->addStock($variant, 5);
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $this->actingAs($user, 'api');

        $order = app(PlaceQuickOrderAction::class)->execute($this->dtoFor($variant));

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('buyer@example.com', $order->customer_email);
    }
}
