<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

class WishlistService
{
    public function add(User $user, Product $product, bool $notify = true): Wishlist
    {
        $currentPrice = $this->getMinPrice($product);

        $user->favorites()->syncWithoutDetaching([
            $product->id => [
                'price_at_add'   => $currentPrice,
                'notify_on_drop' => $notify,
            ],
        ]);

        return Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->firstOrFail();
    }

    public function remove(User $user, Product $product): void
    {
        $user->favorites()->detach($product->id);
    }

    public function toggleNotify(User $user, Product $product): bool
    {
        $pivot = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $newValue = ! $pivot->notify_on_drop;

        $user->favorites()->updateExistingPivot($product->id, [
            'notify_on_drop' => $newValue,
        ]);

        return $newValue;
    }

    public function getPendingSubscriptions(): Collection
    {
        return Wishlist::with(['user', 'product.variants'])
            ->where('notify_on_drop', true)
            ->whereNotNull('price_at_add')
            ->get();
    }

    public function getMinPrice(Product $product): ?float
    {
        return $product->variants()->whereNotNull('price')->min('price');
    }
}
