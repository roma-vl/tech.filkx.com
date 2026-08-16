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
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends BaseApiController
{
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
    public function liqPayCallback(Request $request, HandleLiqPayCallbackAction $action): HttpResponse
    {
        $action->execute(
            (string) $request->input('data'),
            (string) $request->input('signature')
        );

        return response('OK', Response::HTTP_OK);
    }

    public function orderStatus(string $orderNumber): JsonResponse
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->first();

        if (! $order) {
            return self::errorResponse('Замовлення не знайдено', Response::HTTP_NOT_FOUND);
        }

        return self::successfulResponseWithData(new CheckoutOrderResource($order));
    }
}
