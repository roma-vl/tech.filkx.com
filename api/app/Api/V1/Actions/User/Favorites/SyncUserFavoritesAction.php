<?php

namespace App\Api\V1\Actions\User\Favorites;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\User;
use App\Services\WishlistService;
use Illuminate\Database\Eloquent\Collection;

class SyncUserFavoritesAction
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly WishlistService $wishlistService,
        private readonly GetUserFavoritesAction $getUserFavoritesAction
    ) {}

    public function execute(User $user, array $productIds): Collection
    {
        if (! empty($productIds)) {
            // Goes through WishlistService (not a raw syncWithoutDetaching) so newly-synced
            // products get a price_at_add snapshot — without it they're silently excluded from
            // WishlistService::getPendingSubscriptions()'s price-drop notifications.
            $products = $this->productRepository->queryActive()->whereIn('id', $productIds)->get();

            foreach ($products as $product) {
                $this->wishlistService->add($user, $product);
            }
        }

        return $this->getUserFavoritesAction->execute($user);
    }
}
