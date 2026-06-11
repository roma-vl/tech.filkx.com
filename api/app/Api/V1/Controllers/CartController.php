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
use Symfony\Component\HttpFoundation\Response;

class CartController extends BaseApiController
{
    public function show(Request $request, GetCartAction $action): JsonResponse
    {
        $cartDetails = $action->execute(CartSessionDto::fromRequest($request));

        return self::successfulResponseWithData(new CartResource($cartDetails));
    }

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
