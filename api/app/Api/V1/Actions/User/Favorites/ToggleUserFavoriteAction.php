<?php

namespace App\Api\V1\Actions\User\Favorites;

use App\Models\Product;
use App\Models\User;
use App\Services\WishlistService;
use Illuminate\Database\Eloquent\Collection;

class ToggleUserFavoriteAction
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly GetUserFavoritesAction $getUserFavoritesAction
    ) {}

    public function execute(User $user, Product $product): Collection
    {
        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            $this->wishlistService->remove($user, $product);
        } else {
            $this->wishlistService->add($user, $product);
        }

        return $this->getUserFavoritesAction->execute($user);
    }
}
