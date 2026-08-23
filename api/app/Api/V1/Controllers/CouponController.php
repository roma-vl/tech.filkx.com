<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Coupon\ValidateCouponAction;
use App\Api\V1\Dto\ValidateCouponDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Requests\ValidateCouponRequest;
use App\Api\V1\Resources\ValidatedCouponResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends BaseApiController
{
    #[OA\Post(
        path: '/api/v1/coupons/validate',
        summary: 'Validate a coupon code against the current cart',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [
                    new OA\Property(property: 'code', type: 'string'),
                ],
            ),
        ),
        tags: ['Coupons'],
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
                response: 200,
                description: 'Coupon is valid; discount computed against the current cart',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidatedCouponResource'),
            ),
            new OA\Response(
                response: 422,
                description: 'Coupon is invalid, expired, exhausted, or the cart is empty',
            ),
        ],
    )]
    public function validateCoupon(ValidateCouponRequest $request, ValidateCouponAction $action): JsonResponse
    {
        try {
            $result = $action->execute(ValidateCouponDto::fromRequest($request));

            return self::successfulResponseWithData(new ValidatedCouponResource($result));
        } catch (CheckoutValidationException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
