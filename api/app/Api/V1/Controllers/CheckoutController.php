<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Checkout\PlaceOrderAction;
use App\Api\V1\Actions\Checkout\PlaceQuickOrderAction;
use App\Api\V1\Dto\PlaceOrderDto;
use App\Api\V1\Dto\PlaceQuickOrderDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Exceptions\EmptyCartException;
use App\Api\V1\Requests\PlaceOrderRequest;
use App\Api\V1\Resources\CheckoutOrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends BaseApiController
{
    #[OA\Post(
        path: '/api/v1/checkout',
        summary: 'Place an order from the current cart',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customerName', 'customerPhone', 'customerEmail', 'shippingAddress', 'deliveryMethod', 'paymentMethod'],
                properties: [
                    new OA\Property(property: 'customerName', type: 'string', maxLength: 255),
                    new OA\Property(property: 'customerPhone', type: 'string', maxLength: 50),
                    new OA\Property(property: 'customerEmail', type: 'string', format: 'email', maxLength: 255),
                    new OA\Property(property: 'shippingCountry', type: 'string', maxLength: 100, nullable: true),
                    new OA\Property(property: 'shippingCity', type: 'string', maxLength: 100, nullable: true),
                    new OA\Property(property: 'shippingAddress', type: 'string', maxLength: 500),
                    new OA\Property(property: 'deliveryMethod', type: 'string', maxLength: 100),
                    new OA\Property(property: 'paymentMethod', type: 'string', maxLength: 100),
                    new OA\Property(property: 'sessionId', type: 'string', nullable: true, description: 'Alternative to the X-Cart-Session-ID header.'),
                    new OA\Property(property: 'couponCode', type: 'string', nullable: true),
                ],
            ),
        ),
        tags: ['Checkout'],
        parameters: [
            new OA\Parameter(
                name: 'X-Cart-Session-ID',
                description: 'Guest cart session identifier. Omit when authenticated - the customer\'s own cart is used instead.',
                in: 'header',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created from the cart',
                content: new OA\JsonContent(ref: '#/components/schemas/CheckoutOrderResource'),
            ),
            new OA\Response(
                response: 422,
                description: 'Cart is empty or a line item/coupon failed validation (out of stock, inactive product, invalid coupon, ...)',
            ),
        ],
    )]
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

    #[OA\Post(
        path: '/api/v1/checkout/quick',
        summary: 'Place a quick single-item order (no cart, no coupon, fixed Kyiv/Nova Poshta shipping placeholder)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customerName', 'customerPhone', 'variantId'],
                properties: [
                    new OA\Property(property: 'customerName', type: 'string', maxLength: 255),
                    new OA\Property(property: 'customerPhone', type: 'string', maxLength: 50),
                    new OA\Property(property: 'variantId', type: 'integer'),
                    new OA\Property(property: 'paymentMethod', type: 'string', enum: ['cod', 'card'], default: 'cod'),
                ],
            ),
        ),
        tags: ['Checkout'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Quick order created for a single unit of the given variant',
                content: new OA\JsonContent(ref: '#/components/schemas/CheckoutOrderResource'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed, or the variant is unavailable/out of stock',
            ),
        ],
    )]
    public function quickOrder(Request $request, PlaceQuickOrderAction $action): JsonResponse
    {
        $request->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:50',
            'variantId' => 'required|integer|exists:product_variants,id',
            'paymentMethod' => 'sometimes|string|in:cod,card',
        ]);

        try {
            $order = $action->execute(PlaceQuickOrderDto::fromRequest($request));

            return self::successfulResponseWithData(new CheckoutOrderResource($order), Response::HTTP_CREATED);
        } catch (CheckoutValidationException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return self::errorResponse('Помилка при створенні швидкого замовлення: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
