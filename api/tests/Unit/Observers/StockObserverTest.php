<?php

namespace Tests\Unit\Observers;

use App\Jobs\NotifyProductRestockJob;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StockObserverTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    private function makeVariant(Product $product): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => 100,
        ]);
    }

    public function test_dispatches_the_job_when_a_fully_out_of_stock_product_gets_restocked(): void
    {
        Queue::fake();

        $product = $this->makeProduct();
        $variant = $this->makeVariant($product);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $stock = Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
            'reserved' => 0,
        ]);

        $stock->update(['quantity' => 5]);

        Queue::assertPushed(NotifyProductRestockJob::class, fn ($job) => $job->productId === $product->id);
    }

    public function test_does_not_dispatch_when_another_variant_already_had_stock(): void
    {
        $product = $this->makeProduct();
        $outOfStockVariant = $this->makeVariant($product);
        $inStockVariant = $this->makeVariant($product);
        $warehouse = Warehouse::create(['name' => 'Main']);

        // Set up the "already available" variant before faking the queue - creating
        // it is itself a legitimate 0-to-available transition for the product and
        // would otherwise be indistinguishable from the thing under test below.
        Stock::create([
            'variant_id' => $inStockVariant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);
        $emptyStock = Stock::create([
            'variant_id' => $outOfStockVariant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
            'reserved' => 0,
        ]);

        Queue::fake();

        // The product was already purchasable via $inStockVariant, so this row's
        // own transition from 0 isn't a "the product is back in stock" event.
        $emptyStock->update(['quantity' => 3]);

        Queue::assertNotPushed(NotifyProductRestockJob::class);
    }

    public function test_does_not_dispatch_when_stock_is_reduced(): void
    {
        $product = $this->makeProduct();
        $variant = $this->makeVariant($product);
        $warehouse = Warehouse::create(['name' => 'Main']);
        $stock = Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);

        Queue::fake();

        $stock->update(['quantity' => 5]);

        Queue::assertNotPushed(NotifyProductRestockJob::class);
    }
}
