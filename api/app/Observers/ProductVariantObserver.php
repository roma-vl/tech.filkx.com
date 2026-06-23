<?php

namespace App\Observers;

use App\Jobs\NotifyProductWishlistsJob;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    public function updated(ProductVariant $variant): void
    {
        if (! request()->boolean('notify_wishlist', true)) {
            return;
        }

        if (! $variant->wasChanged('price')) {
            return;
        }

        $newPrice = (float) $variant->price;
        $oldPrice = (float) $variant->getOriginal('price');

        if ($newPrice < $oldPrice) {
            NotifyProductWishlistsJob::dispatch($variant->product_id)->afterCommit();
        }
    }
}
