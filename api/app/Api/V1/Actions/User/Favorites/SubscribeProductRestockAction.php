<?php

namespace App\Api\V1\Actions\User\Favorites;

use App\Models\Product;
use App\Models\User;
use App\Services\WishlistService;

class SubscribeProductRestockAction
{
    public function __construct(
        private readonly WishlistService $wishlistService
    ) {}

    public function execute(User $user, Product $product): void
    {
        $this->wishlistService->subscribeToRestock($user, $product);
    }
}
