<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Resources\WishlistItemResource;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends BaseApiController
{
    public function __construct(private readonly WishlistService $wishlistService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $request->user()
            ->favorites()
            ->with('variants')
            ->withPivot(['price_at_add', 'notify_on_drop', 'created_at'])
            ->get();

        return WishlistItemResource::collection($items);
    }

    public function add(Request $request, Product $product): JsonResponse
    {
        $notify = $request->boolean('notify_on_drop', true);
        $item   = $this->wishlistService->add($request->user(), $product, $notify);

        return response()->json([
            'message'      => 'Товар додано до списку бажань',
            'price_at_add' => $item->price_at_add,
            'notify'       => $item->notify_on_drop,
        ]);
    }

    public function remove(Request $request, Product $product): JsonResponse
    {
        $this->wishlistService->remove($request->user(), $product);

        return response()->json(['message' => 'Товар видалено зі списку бажань']);
    }

    public function toggleNotify(Request $request, Product $product): JsonResponse
    {
        $newState = $this->wishlistService->toggleNotify($request->user(), $product);

        return response()->json([
            'notify_on_drop' => $newState,
            'message'        => $newState ? 'Сповіщення увімкнено' : 'Сповіщення вимкнено',
        ]);
    }
}
