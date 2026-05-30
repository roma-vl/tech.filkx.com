<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Checkout\PlaceOrderAction;
use App\Api\V1\Dto\PlaceOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Exceptions\EmptyCartException;
use App\Api\V1\Requests\PlaceOrderRequest;
use App\Api\V1\Resources\CheckoutOrderResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends BaseApiController
{
    public function placeOrder(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        try {
            $order = $action->execute(PlaceOrderDto::fromRequest($request));

            return self::successfulResponseWithData(new CheckoutOrderResource($order), Response::HTTP_CREATED);
        } catch (EmptyCartException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (CheckoutValidationException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return self::errorResponse('Помилка при створенні замовлення: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
