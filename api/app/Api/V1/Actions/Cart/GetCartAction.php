<?php

namespace App\Api\V1\Actions\Cart;

use App\Api\V1\Dto\CartDetailsDto;
use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Repositories\CartRepositoryInterface;
use App\Models\Cart;
use App\Services\Pricing\Dto\CartLineItemDto;
use App\Services\Pricing\PriceCalculationService;
use Illuminate\Support\Str;

class GetCartAction
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected PriceCalculationService $priceCalculationService
    ) {}

    public function execute(CartSessionDto $sessionDto): CartDetailsDto
    {
        $cart = $this->resolveCart($sessionDto);

        $items = $cart->items()->with(['variant.product.categories', 'variant.stocks'])->get();

        $validatedItems = [];
        $total = 0;

        foreach ($items as $item) {
            $variant = $item->variant;
            if (! $variant || $variant->product->status !== 'active') {
                $this->cartRepository->removeItem($item);

                continue;
            }

            // Calculate available stock
            $availableStock = $variant->stocks->sum('quantity') - $variant->stocks->sum('reserved');
            if ($availableStock <= 0) {
                $this->cartRepository->removeItem($item);

                continue;
            }

            // Adjust quantity if exceeds stock
            $quantity = $item->quantity;
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
                $this->cartRepository->updateItemQuantity($item, $quantity);
            }

            // Get product primary image
            $images = $variant->dimensions['images'] ?? [];
            $imageUrl = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop';
            if (! empty($images)) {
                foreach ($images as $img) {
                    if (! empty($img['isPrimary'])) {
                        $imageUrl = $img['url'];
                        break;
                    }
                }
                if ($imageUrl === 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop') {
                    $imageUrl = $images[0]['url'] ?? $imageUrl;
                }
            }

            $price = (float) $variant->price;
            $subtotal = $price * $quantity;
            $total += $subtotal;

            $categoryIds = $variant->product->categories->pluck('id')->all();

            $validatedItems[] = [
                'id' => $item->id,
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'category_ids' => $categoryIds,
                'name' => $variant->product->name['uk'] ?? $variant->product->name['en'] ?? '',
                'slug' => $variant->product->slug,
                'sku' => $variant->sku,
                'price' => $price,
                'oldPrice' => $variant->old_price ? (float) $variant->old_price : null,
                'quantity' => $quantity,
                'stock' => $availableStock,
                'image' => $imageUrl,
                'subtotal' => $subtotal,
            ];
        }

        // Automatic, site-wide Promotions are surfaced here (no coupon code needed) so
        // the cart always reflects the real payable price; a Coupon is only known once
        // the customer enters one, via ValidateCouponAction.
        $priceResult = $this->priceCalculationService->calculate(
            array_map(
                fn (array $item) => new CartLineItemDto(
                    productId: $item['product_id'],
                    categoryIds: $item['category_ids'],
                    unitPrice: $item['price'],
                    quantity: $item['quantity']
                ),
                $validatedItems
            )
        );

        return new CartDetailsDto(
            sessionId: $cart->session_id ?? '',
            items: $validatedItems,
            total: $total,
            promotionDiscount: $priceResult->promotionDiscount,
            discountedTotal: $priceResult->total
        );
    }

    public function resolveCart(CartSessionDto $sessionDto): Cart
    {
        if ($sessionDto->userId) {
            return $this->cartRepository->findOrCreateForUser($sessionDto->userId);
        }

        $sessionId = $sessionDto->sessionId;
        if (! $sessionId) {
            $sessionId = 'anon_'.session_id().'_'.Str::random(16);
        }

        return $this->cartRepository->findOrCreateForSession($sessionId);
    }
}
