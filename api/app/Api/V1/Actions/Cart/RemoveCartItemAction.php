<?php

namespace App\Api\V1\Actions\Cart;

use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Exceptions\CartItemNotFoundException;
use App\Api\V1\Repositories\CartRepositoryInterface;

class RemoveCartItemAction
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected GetCartAction $getCartAction
    ) {}

    public function execute(CartSessionDto $sessionDto, int $itemId): void
    {
        $cart = $this->getCartAction->resolveCart($sessionDto);
        $cartItem = $this->cartRepository->findItem($cart, $itemId);

        if (! $cartItem) {
            throw new CartItemNotFoundException();
        }

        $this->cartRepository->removeItem($cartItem);
    }
}
