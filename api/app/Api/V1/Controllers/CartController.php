<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Cart\AddToCartAction;
use App\Api\V1\Actions\Cart\GetCartAction;
use App\Api\V1\Actions\Cart\MergeCartsAction;
use App\Api\V1\Actions\Cart\RemoveCartItemAction;
use App\Api\V1\Actions\Cart\UpdateCartItemAction;
use App\Api\V1\Dto\AddToCartDto;
use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Dto\MergeCartDto;
use App\Api\V1\Dto\UpdateCartItemDto;
use App\Api\V1\Exceptions\ProductVariantNotFoundException;
use App\Api\V1\Requests\AddToCartRequest;
use App\Api\V1\Requests\MergeCartRequest;
use App\Api\V1\Requests\UpdateCartItemRequest;
use App\Api\V1\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class CartController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/cart',
        summary: 'Get the current cart (guest session or authenticated user)',
        tags: [
            'Cart',
        ],
        parameters: [
            new OA\Parameter(
                name: 'X-Cart-Session-ID',
                description: 'Guest cart session identifier. Ignored for authenticated requests, where the cart is resolved from the bearer token instead.',
                in: 'header',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart contents',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartResource'),
                    ],
                ),
            ),
        ],
    )]
    public function show(Request $request, GetCartAction $action): JsonResponse
    {
        $cartDetails = $action->execute(CartSessionDto::fromRequest($request));

        return self::successfulResponseWithData(new CartResource($cartDetails));
    }

    #[OA\Post(
        path: '/api/v1/cart',
        summary: 'Add a product variant to the cart',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['variantId'],
                properties: [
                    new OA\Property(property: 'variantId', type: 'integer'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 1, minimum: 1),
                    new OA\Property(property: 'sessionId', description: 'Alternative to the X-Cart-Session-ID header', type: 'string'),
                ],
            ),
        ),
        tags: [
            'Cart',
        ],
        parameters: [
            new OA\Parameter(
                name: 'X-Cart-Session-ID',
                description: 'Guest cart session identifier. Ignored for authenticated requests, where the cart is resolved from the bearer token instead.',
                in: 'header',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated cart contents',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartResource'),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Product or variant not found',
            ),
            new OA\Response(
                response: 422,
                description: 'Requested quantity exceeds available stock, or validation failed',
            ),
        ],
    )]
    public function add(AddToCartRequest $request, AddToCartAction $addAction, GetCartAction $getAction): JsonResponse
    {
        $sessionDto = CartSessionDto::fromRequest($request);

        try {
            $addAction->execute($sessionDto, AddToCartDto::fromRequest($request));
        } catch (ProductVariantNotFoundException $e) {
            return self::errorResponse('Товар або варіант не знайдено', Response::HTTP_NOT_FOUND);
        } catch (\RuntimeException $e) {
            return self::errorResponse($e->getMessage(), $e->getCode() ?: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->show($request, $getAction);
    }

    #[OA\Put(
        path: '/api/v1/cart/items/{itemId}',
        summary: 'Update the quantity of a cart item',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1),
                ],
            ),
        ),
        tags: [
            'Cart',
        ],
        parameters: [
            new OA\Parameter(
                name: 'X-Cart-Session-ID',
                description: 'Guest cart session identifier. Ignored for authenticated requests, where the cart is resolved from the bearer token instead.',
                in: 'header',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'itemId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated cart contents',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartResource'),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Item does not exist or does not belong to this cart/session',
            ),
        ],
    )]
    public function updateItem(
        UpdateCartItemRequest $request,
        int $itemId,
        UpdateCartItemAction $updateAction,
        GetCartAction $getAction
    ): JsonResponse {
        $sessionDto = CartSessionDto::fromRequest($request);

        $updateAction->execute($sessionDto, $itemId, UpdateCartItemDto::fromRequest($request));

        return $this->show($request, $getAction);
    }

    #[OA\Delete(
        path: '/api/v1/cart/items/{itemId}',
        summary: 'Remove an item from the cart',
        tags: [
            'Cart',
        ],
        parameters: [
            new OA\Parameter(
                name: 'X-Cart-Session-ID',
                description: 'Guest cart session identifier. Ignored for authenticated requests, where the cart is resolved from the bearer token instead.',
                in: 'header',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'itemId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated cart contents',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartResource'),
                    ],
                ),
            ),
        ],
    )]
    public function removeItem(
        Request $request,
        int $itemId,
        RemoveCartItemAction $removeAction,
        GetCartAction $getAction
    ): JsonResponse {
        $sessionDto = CartSessionDto::fromRequest($request);

        $removeAction->execute($sessionDto, $itemId);

        return $this->show($request, $getAction);
    }

    #[OA\Post(
        path: '/api/v1/cart/merge',
        summary: 'Merge a guest cart into the authenticated user\'s cart',
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['sessionId'],
                properties: [
                    new OA\Property(property: 'sessionId', type: 'string', description: 'Guest cart session id to merge into the authenticated cart'),
                ],
            ),
        ),
        tags: [
            'Cart',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Merged cart contents',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartResource'),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication required',
            ),
        ],
    )]
    public function merge(
        MergeCartRequest $request,
        MergeCartsAction $mergeAction,
        GetCartAction $getAction
    ): JsonResponse {
        try {
            $mergeAction->execute(MergeCartDto::fromRequest($request));
        } catch (\RuntimeException $e) {
            return self::errorResponse($e->getMessage(), $e->getCode() ?: Response::HTTP_UNAUTHORIZED);
        }

        return $this->show($request, $getAction);
    }
}
