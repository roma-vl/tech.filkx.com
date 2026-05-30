<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Coupon\ValidateCouponAction;
use App\Api\V1\Dto\ValidateCouponDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Api\V1\Requests\ValidateCouponRequest;
use App\Api\V1\Resources\ValidatedCouponResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends BaseApiController
{
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
