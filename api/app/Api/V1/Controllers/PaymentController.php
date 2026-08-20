<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Checkout\HandleLiqPayCallbackAction;
use App\Api\V1\Actions\Checkout\InitiateLiqPayPaymentAction;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Resources\CheckoutOrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends BaseApiController
{
    #[OA\Post(
        path: '/api/v1/payments/orders/{orderNumber}/liqpay',
        summary: 'Build a LiqPay checkout payload for an order',
        tags: ['Payments'],
        parameters: [
            new OA\Parameter(
                name: 'orderNumber',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Signed LiqPay checkout payload',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'data', type: 'string', description: 'Base64-encoded LiqPay parameters'),
                                new OA\Property(property: 'signature', type: 'string'),
                                new OA\Property(property: 'checkoutUrl', type: 'string'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Order not found'),
            new OA\Response(response: 422, description: 'Online payment is not configured, or the order is already paid'),
        ],
    )]
    public function initiateLiqPay(string $orderNumber, InitiateLiqPayPaymentAction $action): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return self::errorResponse('Замовлення не знайдено', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $action->execute($order);

            return self::successfulResponseWithData($payload);
        } catch (CheckoutValidationException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * LiqPay server-to-server callback. Public, unauthenticated - authenticity is
     * established by verifying the signature inside the action, not by middleware.
     */
    #[OA\Post(
        path: '/api/v1/payments/liqpay/callback',
        summary: 'LiqPay server-to-server payment status callback',
        tags: ['Payments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'string', description: 'Base64-encoded LiqPay callback payload'),
                    new OA\Property(property: 'signature', type: 'string'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Always returns OK once received, regardless of signature validity, so LiqPay stops retrying',
                content: new OA\MediaType(mediaType: 'text/plain'),
            ),
        ],
    )]
    public function liqPayCallback(Request $request, HandleLiqPayCallbackAction $action): HttpResponse
    {
        $action->execute(
            (string) $request->input('data'),
            (string) $request->input('signature')
        );

        return response('OK', Response::HTTP_OK);
    }

    #[OA\Get(
        path: '/api/v1/payments/orders/{orderNumber}/status',
        summary: 'Get an order\'s current payment/fulfillment status',
        tags: ['Payments'],
        parameters: [
            new OA\Parameter(
                name: 'orderNumber',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order details',
                content: new OA\JsonContent(ref: '#/components/schemas/CheckoutOrderResource'),
            ),
            new OA\Response(response: 404, description: 'Order not found'),
        ],
    )]
    public function orderStatus(string $orderNumber): JsonResponse
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->first();

        if (! $order) {
            return self::errorResponse('Замовлення не знайдено', Response::HTTP_NOT_FOUND);
        }

        return self::successfulResponseWithData(new CheckoutOrderResource($order));
    }
}
