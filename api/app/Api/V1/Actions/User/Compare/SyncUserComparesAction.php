<?php

namespace App\Api\V1\Actions\User\Compare;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SyncUserComparesAction
{
    public function __construct(
        private readonly GetUserComparesAction $getUserComparesAction
    ) {}

    public function execute(User $user, array $productIds): Collection
    {
        if (! empty($productIds)) {
            // A stale client-side id (e.g. from a product removed since the browser
            // last synced) would otherwise hit the compares.product_id foreign key
            // and 500 instead of just being dropped.
            $validIds = Product::whereIn('id', $productIds)->pluck('id');

            if ($validIds->isNotEmpty()) {
                $user->compares()->syncWithoutDetaching($validIds);
            }
        }

        return $this->getUserComparesAction->execute($user);
    }
}
