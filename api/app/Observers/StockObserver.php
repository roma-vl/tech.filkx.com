<?php

namespace App\Observers;

use App\Jobs\NotifyProductRestockJob;
use App\Models\Stock;

class StockObserver
{
    public function created(Stock $stock): void
    {
        $this->handleAvailabilityChange($stock, previouslyAvailable: 0);
    }

    public function updated(Stock $stock): void
    {
        if (! $stock->wasChanged(['quantity', 'reserved'])) {
            return;
        }

        $previouslyAvailable = (int) $stock->getOriginal('quantity') - (int) $stock->getOriginal('reserved');

        $this->handleAvailabilityChange($stock, $previouslyAvailable);
    }

    /**
     * Only worth notifying when the *product as a whole* just went from having
     * nothing available anywhere (across all its variants/warehouses) to having
     * something available - not every time one row's count ticks up while
     * another variant/warehouse already had stock, which wouldn't have shown
     * the product as out of stock to a shopper in the first place.
     */
    private function handleAvailabilityChange(Stock $stock, int $previouslyAvailable): void
    {
        $nowAvailable = $stock->quantity - $stock->reserved;

        if ($nowAvailable <= 0) {
            return;
        }

        $variant = $stock->variant()->withTrashed()->first();
        if (! $variant) {
            return;
        }

        $totalAvailableNow = Stock::query()
            ->join('product_variants', 'product_variants.id', '=', 'stocks.variant_id')
            ->where('product_variants.product_id', $variant->product_id)
            ->selectRaw('SUM(stocks.quantity - stocks.reserved) as total')
            ->value('total');

        $totalAvailableBeforeThisChange = $totalAvailableNow - $nowAvailable + $previouslyAvailable;

        if ($totalAvailableBeforeThisChange > 0) {
            return;
        }

        NotifyProductRestockJob::dispatch($variant->product_id)->afterCommit();
    }
}
