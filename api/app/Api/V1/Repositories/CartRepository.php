<?php

namespace App\Api\V1\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function findOrCreateForUser(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function findOrCreateForSession(string $sessionId): Cart
    {
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function findBySessionId(string $sessionId): ?Cart
    {
        return Cart::where('session_id', $sessionId)->first();
    }

    public function findItem(Cart $cart, int $itemId): ?CartItem
    {
        return CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->first();
    }

    public function findItemByVariant(Cart $cart, int $variantId): ?CartItem
    {
        return CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $variantId)
            ->first();
    }

    public function addItem(Cart $cart, int $variantId, int $quantity): CartItem
    {
        return CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variantId,
            'quantity' => $quantity,
        ]);
    }

    public function updateItemQuantity(CartItem $item, int $quantity): bool
    {
        return $item->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $item): bool
    {
        return (bool) $item->delete();
    }

    public function mergeCarts(Cart $userCart, Cart $anonCart): void
    {
        DB::transaction(function () use ($userCart, $anonCart) {
            $anonItems = $anonCart->items;
            foreach ($anonItems as $anonItem) {
                $userItem = $this->findItemByVariant($userCart, $anonItem->variant_id);

                if ($userItem) {
                    $userItem->update([
                        'quantity' => $userItem->quantity + $anonItem->quantity,
                    ]);
                    $anonItem->delete();
                } else {
                    $anonItem->update([
                        'cart_id' => $userCart->id,
                    ]);
                }
            }
            $anonCart->delete();
        });
    }
}
