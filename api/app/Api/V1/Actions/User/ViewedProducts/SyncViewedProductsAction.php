<?php

namespace App\Api\V1\Actions\User\ViewedProducts;

use App\Models\User;
use Illuminate\Support\Collection;

class SyncViewedProductsAction
{
    public function __construct(
        private readonly GetUserViewedProductsAction $getUserViewedProductsAction
    ) {}

    /**
     * Merges a client-supplied viewing history (e.g. collected while the user was
     * a guest) into the user's server-side history, keeping the higher view count
     * and the most recent timestamp for products seen on both sides.
     */
    public function execute(User $user, array $items): Collection
    {
        foreach ($items as $item) {
            $productId = $item['id'] ?? null;
            if (! $productId) {
                continue;
            }

            $existing = $user->viewedProducts()->where('product_id', $productId)->first();

            if ($existing) {
                $newCount = max($existing->pivot->view_count, $item['view_count'] ?? 1);
                $newTime = ! empty($item['last_viewed_at']) && strtotime($item['last_viewed_at']) > $existing->pivot->updated_at->timestamp
                    ? date('Y-m-d H:i:s', strtotime($item['last_viewed_at']))
                    : $existing->pivot->updated_at;

                $user->viewedProducts()->updateExistingPivot($productId, [
                    'view_count' => $newCount,
                    'updated_at' => $newTime,
                ]);

                continue;
            }

            $timestamp = ! empty($item['last_viewed_at']) ? date('Y-m-d H:i:s', strtotime($item['last_viewed_at'])) : now();
            $user->viewedProducts()->attach($productId, [
                'view_count' => $item['view_count'] ?? 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        return $this->getUserViewedProductsAction->execute($user);
    }
}
