<?php

namespace App\Api\V1\Actions\User\ViewedProducts;

use App\Models\User;

class TrackViewedProductAction
{
    public function execute(User $user, int $productId): void
    {
        $existing = $user->viewedProducts()->where('product_id', $productId)->first();

        if ($existing) {
            $user->viewedProducts()->updateExistingPivot($productId, [
                'view_count' => $existing->pivot->view_count + 1,
                'updated_at' => now(),
            ]);

            return;
        }

        $user->viewedProducts()->attach($productId, [
            'view_count' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
