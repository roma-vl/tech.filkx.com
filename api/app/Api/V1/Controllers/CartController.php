<?php

namespace App\Api\V1\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CartController extends BaseApiController
{
    private function getCart(Request $request): Cart
    {
        $user = auth('api')->user();
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        $sessionId = $request->header('X-Cart-Session-ID') ?: $request->input('session_id');
        if (! $sessionId) {
            $sessionId = 'anon_'.session_id().'_'.Str::random(16);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function show(Request $request): JsonResponse
    {
        $cart = $this->getCart($request);
        $items = $cart->items()->with(['variant.product', 'variant.stocks'])->get();

        $validatedItems = [];
        $total = 0;

        foreach ($items as $item) {
            $variant = $item->variant;
            if (! $variant || $variant->product->status !== 'active') {
                $item->delete();

                continue;
            }

            // Calculate available stock
            $availableStock = $variant->stocks->sum('quantity') - $variant->stocks->sum('reserved');
            if ($availableStock <= 0) {
                $item->delete();

                continue;
            }

            // Adjust quantity if exceeds stock
            $quantity = $item->quantity;
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
                $item->update(['quantity' => $quantity]);
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

            $validatedItems[] = [
                'id' => $item->id,
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'name' => $variant->product->name['uk'] ?? $variant->product->name['en'] ?? '',
                'sku' => $variant->sku,
                'price' => $price,
                'oldPrice' => $variant->old_price ? (float) $variant->old_price : null,
                'quantity' => $quantity,
                'stock' => $availableStock,
                'image' => $imageUrl,
                'subtotal' => $subtotal,
            ];
        }

        return self::successfulResponseWithData([
            'sessionId' => $cart->session_id,
            'items' => $validatedItems,
            'total' => $total,
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $id = $request->input('variant_id');
        $quantity = $request->input('quantity', 1);

        $variant = ProductVariant::with('stocks')->find($id);

        if (! $variant) {
            $product = Product::with('variants.stocks')->find($id);
            if ($product && $product->variants->isNotEmpty()) {
                $variant = $product->variants->first();
            }
        }

        if (! $variant) {
            return self::errorResponse('Товар або варіант не знайдено', Response::HTTP_NOT_FOUND);
        }

        $variantId = $variant->id;
        $availableStock = $variant->stocks->sum('quantity') - $variant->stocks->sum('reserved');

        if ($availableStock <= 0) {
            return self::errorResponse('Товару немає в наявності', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cart = $this->getCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $variantId)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > $availableStock) {
                $newQuantity = $availableStock;
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
            }
            CartItem::create([
                'cart_id' => $cart->id,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }

        return $this->show($request);
    }

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $variant = ProductVariant::with('stocks')->findOrFail($cartItem->variant_id);
        $availableStock = $variant->stocks->sum('quantity') - $variant->stocks->sum('reserved');

        $quantity = $request->input('quantity');
        if ($quantity > $availableStock) {
            $quantity = $availableStock;
        }

        $cartItem->update(['quantity' => $quantity]);

        return $this->show($request);
    }

    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->getCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $cartItem->delete();

        return $this->show($request);
    }

    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $user = auth('api')->user();
        if (! $user) {
            return self::errorResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        $anonCart = Cart::where('session_id', $request->input('session_id'))->first();

        if ($anonCart && $anonCart->id !== $userCart->id) {
            $anonItems = $anonCart->items;
            foreach ($anonItems as $anonItem) {
                $userItem = CartItem::where('cart_id', $userCart->id)
                    ->where('variant_id', $anonItem->variant_id)
                    ->first();

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
        }

        return $this->show($request);
    }
}
