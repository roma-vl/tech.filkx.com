<?php

namespace App\Api\V1\Controllers;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends BaseApiController
{
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $code = $request->input('code');
        $cartTotal = $request->input('cart_total');

        $coupon = Coupon::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return self::errorResponse('Купон недійсний або неактивний', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return self::errorResponse('Термін дії купона закінчився', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return self::errorResponse('Купон вичерпав ліміт використання', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $discount = 0;
        if ($coupon->type === 'percent') {
            $discount = $cartTotal * ($coupon->amount / 100);
        } else {
            $discount = $coupon->amount;
        }

        if ($discount > $cartTotal) {
            $discount = $cartTotal;
        }

        return self::successfulResponseWithData([
            'code' => $coupon->code,
            'type' => $coupon->type,
            'amount' => $coupon->amount,
            'discount' => $discount,
        ]);
    }
}
