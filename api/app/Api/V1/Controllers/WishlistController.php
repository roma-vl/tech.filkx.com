<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Resources\WishlistItemResource;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class WishlistController extends BaseApiController
{
    public function __construct(private readonly WishlistService $wishlistService) {}

    #[OA\Get(
        path: '/api/v1/wishlist',
        summary: 'List the current user\'s wishlist',
        security: [['bearerAuth' => []]],
        tags: ['Wishlist'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wishlist items',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/WishlistItemResource'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $request->user()
            ->favorites()
            ->with('variants')
            ->withPivot(['price_at_add', 'notify_on_drop', 'created_at'])
            ->get();

        return WishlistItemResource::collection($items);
    }

    #[OA\Post(
        path: '/api/v1/wishlist/{product}',
        summary: 'Add a product to the wishlist',
        security: [['bearerAuth' => []]],
        tags: ['Wishlist'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'notify_on_drop', type: 'boolean', default: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product added to the wishlist',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'price_at_add', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'notify', type: 'boolean'),
                    ],
                ),
            ),
        ],
    )]
    public function add(Request $request, Product $product): JsonResponse
    {
        $notify = $request->boolean('notify_on_drop', true);
        $item = $this->wishlistService->add($request->user(), $product, $notify);

        return response()->json([
            'message' => 'Товар додано до списку бажань',
            'price_at_add' => $item->price_at_add,
            'notify' => $item->notify_on_drop,
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/wishlist/{product}',
        summary: 'Remove a product from the wishlist',
        security: [['bearerAuth' => []]],
        tags: ['Wishlist'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product removed from the wishlist',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                    ],
                ),
            ),
        ],
    )]
    public function remove(Request $request, Product $product): JsonResponse
    {
        $this->wishlistService->remove($request->user(), $product);

        return response()->json(['message' => 'Товар видалено зі списку бажань']);
    }

    #[OA\Patch(
        path: '/api/v1/wishlist/{product}/notify',
        summary: 'Toggle price-drop notifications for a wishlisted product',
        security: [['bearerAuth' => []]],
        tags: ['Wishlist'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification preference toggled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'notify_on_drop', type: 'boolean'),
                        new OA\Property(property: 'message', type: 'string'),
                    ],
                ),
            ),
        ],
    )]
    public function toggleNotify(Request $request, Product $product): JsonResponse
    {
        $newState = $this->wishlistService->toggleNotify($request->user(), $product);

        return response()->json([
            'notify_on_drop' => $newState,
            'message' => $newState ? 'Сповіщення увімкнено' : 'Сповіщення вимкнено',
        ]);
    }
}
