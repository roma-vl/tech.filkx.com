<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\OrderRepository;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(OrderRepository::class);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $order = Order::create(array_merge([
            'order_number' => 'FKX-'.strtoupper(Str::random(10)),
            'customer_name' => 'Іван Петренко',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'card',
            'payment_status' => 'pending',
            'status' => 'pending',
            'total_price' => 500,
        ], $overrides));

        if ($createdAt !== null) {
            $order->created_at = $createdAt;
            $order->save();
        }

        return $order;
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
            'sku' => 'SKU-'.uniqid(),
            'price' => 100,
        ]);
    }

    // --- allWithItems ---

    public function test_all_with_items_returns_orders_ordered_by_created_at_desc_with_items_loaded(): void
    {
        $older = $this->makeOrder(['created_at' => now()->subDay()]);
        $newer = $this->makeOrder(['created_at' => now()]);
        OrderItem::create([
            'order_id' => $older->id,
            'product_name' => 'Item',
            'sku' => 'SKU-1',
            'price' => 100,
            'quantity' => 1,
        ]);

        $result = $this->repository->allWithItems();

        $this->assertCount(2, $result);
        $this->assertSame($newer->id, $result->first()->id);
        $this->assertSame($older->id, $result->last()->id);
        $this->assertTrue($result->last()->relationLoaded('items'));
        $this->assertCount(1, $result->last()->items);
    }

    public function test_all_with_items_returns_empty_collection_when_no_orders_exist(): void
    {
        $result = $this->repository->allWithItems();

        $this->assertCount(0, $result);
    }

    // --- findWithItems ---

    public function test_find_with_items_loads_items_variant_and_stocks(): void
    {
        $order = $this->makeOrder();
        $variant = $this->makeVariant();
        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => 'Item',
            'sku' => $variant->sku,
            'price' => 100,
            'quantity' => 2,
        ]);

        $result = $this->repository->findWithItems($order->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('items'));
        $this->assertTrue($result->items->first()->relationLoaded('variant'));
        $this->assertTrue($result->items->first()->variant->relationLoaded('stocks'));
    }

    public function test_find_with_items_returns_null_when_order_does_not_exist(): void
    {
        $result = $this->repository->findWithItems(999999);

        $this->assertNull($result);
    }

    // --- find ---

    public function test_find_returns_the_order(): void
    {
        $order = $this->makeOrder();

        $result = $this->repository->find($order->id);

        $this->assertNotNull($result);
        $this->assertSame($order->id, $result->id);
    }

    public function test_find_returns_null_when_order_does_not_exist(): void
    {
        $result = $this->repository->find(999999);

        $this->assertNull($result);
    }

    // --- update ---

    public function test_update_persists_the_given_data_and_returns_the_order(): void
    {
        $order = $this->makeOrder(['status' => 'pending']);

        $result = $this->repository->update($order, ['status' => 'completed']);

        $this->assertSame('completed', $result->status);
        $this->assertSame('completed', $order->fresh()->status);
    }

    // --- delete ---

    public function test_delete_removes_the_order_and_returns_true(): void
    {
        $order = $this->makeOrder();

        $result = $this->repository->delete($order);

        $this->assertTrue($result);
        $this->assertNull(Order::find($order->id));
    }

    // --- paginateLedger ---

    public function test_paginate_ledger_defaults_to_completed_and_cancelled_orders_when_no_type_filter(): void
    {
        $completed = $this->makeOrder(['status' => 'completed']);
        $cancelled = $this->makeOrder(['status' => 'cancelled']);
        $this->makeOrder(['status' => 'pending']);

        $result = $this->repository->paginateLedger([], 15);

        $this->assertSame(2, $result->total());
        $ids = $result->getCollection()->pluck('id')->all();
        $this->assertContains($completed->id, $ids);
        $this->assertContains($cancelled->id, $ids);
    }

    public function test_paginate_ledger_filters_by_type_charge_maps_to_completed_status(): void
    {
        $completed = $this->makeOrder(['status' => 'completed']);
        $this->makeOrder(['status' => 'cancelled']);

        $result = $this->repository->paginateLedger(['type' => 'charge'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($completed->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_ledger_filters_by_type_refund_maps_to_cancelled_status(): void
    {
        $this->makeOrder(['status' => 'completed']);
        $cancelled = $this->makeOrder(['status' => 'cancelled']);

        $result = $this->repository->paginateLedger(['type' => 'refund'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($cancelled->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_ledger_returns_nothing_for_an_unrecognised_type(): void
    {
        $this->makeOrder(['status' => 'completed']);
        $this->makeOrder(['status' => 'cancelled']);

        $result = $this->repository->paginateLedger(['type' => 'bogus'], 15);

        $this->assertSame(0, $result->total());
    }

    public function test_paginate_ledger_filters_by_user_id(): void
    {
        $user = User::factory()->create();
        $mine = $this->makeOrder(['status' => 'completed', 'user_id' => $user->id]);
        $this->makeOrder(['status' => 'completed']);

        $result = $this->repository->paginateLedger(['user_id' => $user->id], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($mine->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_ledger_filters_by_date_range(): void
    {
        $inRange = $this->makeOrder(['status' => 'completed', 'created_at' => now()->subDays(2)]);
        $this->makeOrder(['status' => 'completed', 'created_at' => now()->subDays(10)]);
        $this->makeOrder(['status' => 'completed', 'created_at' => now()]);

        $result = $this->repository->paginateLedger([
            'from' => now()->subDays(5)->toDateString(),
            'to' => now()->subDay()->toDateString(),
        ], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($inRange->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_ledger_orders_by_created_at_desc_and_loads_user(): void
    {
        $user = User::factory()->create();
        $older = $this->makeOrder(['status' => 'completed', 'created_at' => now()->subDay(), 'user_id' => $user->id]);
        $newer = $this->makeOrder(['status' => 'completed', 'created_at' => now()]);

        $result = $this->repository->paginateLedger([], 15);

        $this->assertSame($newer->id, $result->getCollection()->first()->id);
        $this->assertSame($older->id, $result->getCollection()->last()->id);
        $this->assertTrue($result->getCollection()->last()->relationLoaded('user'));
    }

    public function test_paginate_ledger_respects_per_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makeOrder(['status' => 'completed']);
        }

        $result = $this->repository->paginateLedger([], 2);

        $this->assertSame(3, $result->total());
        $this->assertCount(2, $result->getCollection());
        $this->assertSame(2, $result->lastPage());
    }

    // --- paginateInvoices ---

    public function test_paginate_invoices_returns_all_orders_by_default(): void
    {
        $this->makeOrder();
        $this->makeOrder();

        $result = $this->repository->paginateInvoices([], 15);

        $this->assertSame(2, $result->total());
    }

    public function test_paginate_invoices_searches_by_order_number(): void
    {
        $target = $this->makeOrder(['order_number' => 'FKX-FINDME123']);
        $this->makeOrder(['order_number' => 'FKX-OTHER456']);

        $result = $this->repository->paginateInvoices(['search' => 'FINDME'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($target->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_invoices_searches_by_customer_name(): void
    {
        $target = $this->makeOrder(['customer_name' => 'Unique Customer Name']);
        $this->makeOrder(['customer_name' => 'Someone Else']);

        $result = $this->repository->paginateInvoices(['search' => 'Unique Customer'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($target->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_invoices_searches_by_customer_email(): void
    {
        $target = $this->makeOrder(['customer_email' => 'findme@example.com']);
        $this->makeOrder(['customer_email' => 'other@example.com']);

        $result = $this->repository->paginateInvoices(['search' => 'findme@'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($target->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_invoices_filters_by_status_paid_maps_to_completed(): void
    {
        $paid = $this->makeOrder(['status' => 'completed']);
        $this->makeOrder(['status' => 'pending']);

        $result = $this->repository->paginateInvoices(['status' => 'paid'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($paid->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_invoices_filters_by_status_issued_maps_to_pending(): void
    {
        $issued = $this->makeOrder(['status' => 'pending']);
        $this->makeOrder(['status' => 'completed']);

        $result = $this->repository->paginateInvoices(['status' => 'issued'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($issued->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_invoices_filters_by_status_cancelled(): void
    {
        $cancelled = $this->makeOrder(['status' => 'cancelled']);
        $this->makeOrder(['status' => 'completed']);

        $result = $this->repository->paginateInvoices(['status' => 'cancelled'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($cancelled->id, $result->getCollection()->first()->id);
    }

    public function test_paginate_invoices_ignores_unrecognised_status(): void
    {
        $this->makeOrder(['status' => 'completed']);
        $this->makeOrder(['status' => 'pending']);

        $result = $this->repository->paginateInvoices(['status' => 'bogus'], 15);

        $this->assertSame(2, $result->total());
    }

    public function test_paginate_invoices_returns_empty_when_search_matches_nothing(): void
    {
        $this->makeOrder();

        $result = $this->repository->paginateInvoices(['search' => 'no-such-order'], 15);

        $this->assertSame(0, $result->total());
    }

    // --- paginatePendingPayments ---

    public function test_paginate_pending_payments_returns_only_pending_orders_with_user_loaded(): void
    {
        $user = User::factory()->create();
        $pending = $this->makeOrder(['status' => 'pending', 'user_id' => $user->id]);
        $this->makeOrder(['status' => 'completed']);

        $result = $this->repository->paginatePendingPayments(15);

        $this->assertSame(1, $result->total());
        $first = $result->getCollection()->first();
        $this->assertSame($pending->id, $first->id);
        $this->assertTrue($first->relationLoaded('user'));
    }

    public function test_paginate_pending_payments_returns_empty_when_none_pending(): void
    {
        $this->makeOrder(['status' => 'completed']);

        $result = $this->repository->paginatePendingPayments(15);

        $this->assertSame(0, $result->total());
    }

    // --- getAccountingStats ---

    public function test_get_accounting_stats_computes_revenue_refunds_and_net_in_minor_units(): void
    {
        $this->makeOrder(['status' => 'completed', 'total_price' => 100]);
        $this->makeOrder(['status' => 'completed', 'total_price' => 250.50]);
        $this->makeOrder(['status' => 'cancelled', 'total_price' => 50]);
        $this->makeOrder(['status' => 'pending', 'total_price' => 999]);

        $stats = $this->repository->getAccountingStats();

        $this->assertSame(35050, $stats['totalRevenueMinor']);
        $this->assertSame(5000, $stats['totalRefundsMinor']);
        $this->assertSame(30050, $stats['netRevenueMinor']);
    }

    public function test_get_accounting_stats_returns_zeroes_when_no_orders_exist(): void
    {
        $stats = $this->repository->getAccountingStats();

        $this->assertSame(0, $stats['totalRevenueMinor']);
        $this->assertSame(0, $stats['totalRefundsMinor']);
        $this->assertSame(0, $stats['netRevenueMinor']);
    }

    // --- getBillingStats ---

    public function test_get_billing_stats_computes_revenue_and_pending_count(): void
    {
        $this->makeOrder(['status' => 'completed', 'total_price' => 100]);
        $this->makeOrder(['status' => 'pending']);
        $this->makeOrder(['status' => 'pending']);
        $this->makeOrder(['status' => 'cancelled', 'total_price' => 999]);

        $stats = $this->repository->getBillingStats();

        $this->assertSame(10000, $stats['revenueMinor']);
        $this->assertSame(0, $stats['activeSubscriptions']);
        $this->assertSame(2, $stats['pendingPaymentsCount']);
    }

    public function test_get_billing_stats_returns_zeroes_when_no_orders_exist(): void
    {
        $stats = $this->repository->getBillingStats();

        $this->assertSame(0, $stats['revenueMinor']);
        $this->assertSame(0, $stats['activeSubscriptions']);
        $this->assertSame(0, $stats['pendingPaymentsCount']);
    }

    // --- getCompletedAndCancelledOrders ---

    public function test_get_completed_and_cancelled_orders_returns_only_those_statuses_with_user_loaded(): void
    {
        $user = User::factory()->create();
        $completed = $this->makeOrder(['status' => 'completed', 'user_id' => $user->id]);
        $cancelled = $this->makeOrder(['status' => 'cancelled']);
        $this->makeOrder(['status' => 'pending']);

        $result = $this->repository->getCompletedAndCancelledOrders();

        $this->assertCount(2, $result);
        $ids = $result->pluck('id')->all();
        $this->assertContains($completed->id, $ids);
        $this->assertContains($cancelled->id, $ids);
        $this->assertTrue($result->first()->relationLoaded('user'));
    }

    public function test_get_completed_and_cancelled_orders_returns_empty_when_none_match(): void
    {
        $this->makeOrder(['status' => 'pending']);

        $result = $this->repository->getCompletedAndCancelledOrders();

        $this->assertCount(0, $result);
    }
}
