<?php

namespace App\Api\V1\Actions\Cart;

use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Dto\UpdateCartItemDto;
use App\Api\V1\Exceptions\CartItemNotFoundException;
use App\Api\V1\Repositories\CartRepositoryInterface;
use App\Models\ProductVariant;

class UpdateCartItemAction
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected GetCartAction $getCartAction
    ) {}

    public function execute(CartSessionDto $sessionDto, int $itemId, UpdateCartItemDto $dto): void
    {
        $cart = $this->getCartAction->resolveCart($sessionDto);
        $cartItem = $this->cartRepository->findItem($cart, $itemId);

        if (! $cartItem) {
            throw new CartItemNotFoundException;
        }

        $variant = ProductVariant::with('stocks')->findOrFail($cartItem->variant_id);
        $availableStock = $variant->stocks->sum('quantity') - $variant->stocks->sum('reserved');

        $quantity = $dto->quantity;
        if ($quantity > $availableStock) {
            $quantity = $availableStock;
        }

        $this->cartRepository->updateItemQuantity($cartItem, $quantity);
    }
}
