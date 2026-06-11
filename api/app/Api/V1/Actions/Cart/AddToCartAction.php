<?php

namespace App\Api\V1\Actions\Cart;

use App\Api\V1\Dto\AddToCartDto;
use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Exceptions\ProductVariantNotFoundException;
use App\Api\V1\Repositories\CartRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Symfony\Component\HttpFoundation\Response;

class AddToCartAction
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected GetCartAction $getCartAction
    ) {}

    public function execute(CartSessionDto $sessionDto, AddToCartDto $dto): void
    {
        $id = $dto->variantId;
        $quantity = $dto->quantity;

        $variant = ProductVariant::with('stocks')->find($id);

        if (! $variant) {
            $product = Product::with('variants.stocks')->find($id);
            if ($product && $product->variants->isNotEmpty()) {
                $variant = $product->variants->first();
            }
        }

        if (! $variant) {
            throw new ProductVariantNotFoundException('Товар або варіант не знайдено');
        }

        $variantId = $variant->id;
        $availableStock = $variant->stocks->sum('quantity') - $variant->stocks->sum('reserved');

        if ($availableStock <= 0) {
            throw new \RuntimeException('Товару немає в наявності', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cart = $this->getCartAction->resolveCart($sessionDto);
        $cartItem = $this->cartRepository->findItemByVariant($cart, $variantId);

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > $availableStock) {
                $newQuantity = $availableStock;
            }
            $this->cartRepository->updateItemQuantity($cartItem, $newQuantity);
        } else {
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
            }
            $this->cartRepository->addItem($cart, $variantId, $quantity);
        }
    }
}
