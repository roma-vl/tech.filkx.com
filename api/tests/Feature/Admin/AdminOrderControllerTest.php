<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->roles()->attach($adminRole->id);

        return $user;
    }

    private function makeCustomer(): User
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function makeOrderWithStock(string $status, int $quantity = 1, int $stockQuantity = 10, int $reserved = 1): array
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
        $warehouse = Warehouse::create(['name' => 'Main']);
        $stock = Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $stockQuantity,
            'reserved' => $reserved,
        ]);

        $order = Order::create([
            'order_number' => 'FKX-'.uniqid(),
            'customer_name' => 'Іван',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => $status,
            'total_price' => 100 * $quantity,
            'discount_amount' => 0,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => 'Товар',
            'sku' => $variant->sku,
            'price' => 100,
            'quantity' => $quantity,
        ]);

        return [$order, $stock];
    }

    public function test_index_requires_admin_role(): void
    {
        $customer = $this->makeCustomer();

        $this->withHeaders($this->authHeader($customer))
            ->getJson('/api/admin/orders')
            ->assertForbidden();
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/admin/orders')->assertUnauthorized();
    }

    public function test_index_lists_orders_for_an_admin(): void
    {
        [$order] = $this->makeOrderWithStock('pending_payment');
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))->getJson('/api/admin/orders');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.orderNumber', $order->order_number);
    }

    public function test_show_returns_order_details(): void
    {
        [$order] = $this->makeOrderWithStock('pending_payment');
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))->getJson("/api/admin/orders/{$order->id}");

        $response->assertOk()->assertJsonPath('data.orderNumber', $order->order_number);
    }

    public function test_show_returns_404_for_a_missing_order(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/orders/999999')
            ->assertStatus(404);
    }

    public function test_transitioning_from_pending_payment_to_paid_converts_the_stock_reservation(): void
    {
        [$order, $stock] = $this->makeOrderWithStock('pending_payment', quantity: 2, stockQuantity: 10, reserved: 2);
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'paid']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.paymentStatus', 'paid');

        $stock->refresh();
        $this->assertSame(8, $stock->quantity);
        $this->assertSame(0, $stock->reserved);
    }

    public function test_transitioning_from_pending_payment_to_cancelled_releases_the_reservation_without_touching_quantity(): void
    {
        [$order, $stock] = $this->makeOrderWithStock('pending_payment', quantity: 2, stockQuantity: 10, reserved: 2);
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'cancelled']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.paymentStatus', 'failed');

        $stock->refresh();
        $this->assertSame(10, $stock->quantity);
        $this->assertSame(0, $stock->reserved);
    }

    public function test_transitioning_from_paid_to_cancelled_returns_stock_to_inventory(): void
    {
        [$order, $stock] = $this->makeOrderWithStock('paid', quantity: 3, stockQuantity: 7, reserved: 0);
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'cancelled']);

        $response->assertOk();

        $stock->refresh();
        $this->assertSame(10, $stock->quantity);
    }

    public function test_updating_to_the_same_status_does_not_touch_stock(): void
    {
        [$order, $stock] = $this->makeOrderWithStock('paid', quantity: 2, stockQuantity: 5, reserved: 0);
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'paid'])
            ->assertOk();

        $stock->refresh();
        $this->assertSame(5, $stock->quantity);
    }

    public function test_update_status_rejects_an_invalid_status_value(): void
    {
        [$order] = $this->makeOrderWithStock('pending_payment');
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'not-a-real-status'])
            ->assertStatus(422);
    }

    public function test_update_status_can_set_carrier_and_tracking_number(): void
    {
        [$order] = $this->makeOrderWithStock('paid');
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))
            ->putJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'shipped',
                'carrier' => 'Nova Poshta',
                'tracking_number' => '20450123456789',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.carrier', 'Nova Poshta')
            ->assertJsonPath('data.trackingNumber', '20450123456789');
    }

    public function test_destroy_deletes_an_order(): void
    {
        [$order] = $this->makeOrderWithStock('pending_payment');
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->deleteJson("/api/admin/orders/{$order->id}")
            ->assertOk();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_destroy_returns_404_for_a_missing_order(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->deleteJson('/api/admin/orders/999999')
            ->assertStatus(404);
    }
}
