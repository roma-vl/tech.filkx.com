<?php

namespace App\Api\V1\Repositories;

use App\Models\Cart;
use App\Models\CartItem;

interface CartRepositoryInterface
{
    public function findOrCreateForUser(int $userId): Cart;

    public function findOrCreateForSession(string $sessionId): Cart;

    public function findBySessionId(string $sessionId): ?Cart;

    public function findItem(Cart $cart, int $itemId): ?CartItem;

    public function findItemByVariant(Cart $cart, int $variantId): ?CartItem;

    public function addItem(Cart $cart, int $variantId, int $quantity): CartItem;

    public function updateItemQuantity(CartItem $item, int $quantity): bool;

    public function removeItem(CartItem $item): bool;

    public function mergeCarts(Cart $userCart, Cart $anonCart): void;
}
