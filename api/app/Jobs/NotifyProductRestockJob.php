<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Wishlist;
use App\Notifications\BackInStockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyProductRestockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $productId) {}

    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (! $product) {
            return;
        }

        $subscriptions = Wishlist::with('user')
            ->where('product_id', $this->productId)
            ->where('notify_on_restock', true)
            ->get();

        foreach ($subscriptions as $item) {
            $item->user->notify(new BackInStockNotification($product));

            // One-shot: don't notify again on every subsequent restock unless the
            // user asks to be notified again from the product page.
            $item->user->favorites()->updateExistingPivot($item->product_id, [
                'notify_on_restock' => false,
            ]);
        }

        Log::info("NotifyProductRestockJob: product_id={$this->productId}, notified: {$subscriptions->count()}");
    }
}
