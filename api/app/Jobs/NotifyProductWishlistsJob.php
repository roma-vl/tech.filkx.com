<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Wishlist;
use App\Notifications\PriceDropNotification;
use App\Services\WishlistService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyProductWishlistsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $productId) {}

    public function handle(WishlistService $wishlistService): void
    {
        $product = Product::find($this->productId);

        if (! $product) {
            return;
        }

        $threshold    = config('wishlist.price_drop_threshold', 5.0);
        $currentPrice = $wishlistService->getMinPrice($product);

        if ($currentPrice === null) {
            return;
        }

        $subscriptions = Wishlist::with('user')
            ->where('product_id', $this->productId)
            ->where('notify_on_drop', true)
            ->get();

        $notified  = 0;
        $backfilled = 0;

        foreach ($subscriptions as $item) {
            if ($item->price_at_add === null) {
                // Ціна не збережена — запам'ятовуємо поточну як базову, лист не надсилаємо
                $item->user->favorites()->updateExistingPivot($item->product_id, [
                    'price_at_add' => $currentPrice,
                ]);
                $backfilled++;
                continue;
            }

            if (! $item->hasPriceDrop($threshold)) {
                continue;
            }

            $item->user->notify(new PriceDropNotification(
                product:  $product,
                oldPrice: (float) $item->price_at_add,
                newPrice: $currentPrice,
            ));

            $item->user->favorites()->updateExistingPivot($item->product_id, [
                'price_at_add' => $currentPrice,
            ]);

            $notified++;
        }

        Log::info("NotifyProductWishlistsJob: product_id={$this->productId}, backfilled: {$backfilled}, надіслано: {$notified}");
    }
}
