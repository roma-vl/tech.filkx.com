<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Marketing\CreateCouponAction;
use App\Api\Admin\Actions\Marketing\CreatePromotionAction;
use App\Api\Admin\Actions\Marketing\DeleteCouponAction;
use App\Api\Admin\Actions\Marketing\DeletePromotionAction;
use App\Api\Admin\Actions\Marketing\ListCouponsAction;
use App\Api\Admin\Actions\Marketing\ListPromotionsAction;
use App\Api\Admin\Actions\Marketing\UpdateCouponAction;
use App\Api\Admin\Actions\Marketing\UpdatePromotionAction;
use App\Api\Admin\Dto\CouponDto;
use App\Api\Admin\Dto\MarketingFilterDto;
use App\Api\Admin\Dto\PromotionDto;
use App\Api\Admin\Requests\MarketingFilterRequest;
use App\Api\Admin\Requests\StoreCouponRequest;
use App\Api\Admin\Requests\StorePromotionRequest;
use App\Api\Admin\Requests\UpdateCouponRequest;
use App\Api\Admin\Requests\UpdatePromotionRequest;
use App\Api\Admin\Resources\CouponResource;
use App\Api\Admin\Resources\PromotionResource;
use Illuminate\Http\JsonResponse;

class AdminMarketingController extends BaseApiController
{
    // --- Coupons ---
    public function coupons(MarketingFilterRequest $request, ListCouponsAction $action): JsonResponse
    {
        $paginator = $action->execute(MarketingFilterDto::fromRequest($request), self::PER_PAGE);

        return self::successfulResponseWithData([
            'data' => CouponResource::collection($paginator->items()),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function storeCoupon(StoreCouponRequest $request, CreateCouponAction $action): JsonResponse
    {
        $coupon = $action->execute(CouponDto::fromRequest($request));

        return self::successfulResponseWithData($coupon);
    }

    public function updateCoupon(UpdateCouponRequest $request, int $id, UpdateCouponAction $action): JsonResponse
    {
        $coupon = $action->execute($id, CouponDto::fromRequest($request));

        return self::successfulResponseWithData($coupon);
    }

    public function destroyCoupon(int $id, DeleteCouponAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse('Coupon deleted successfully.');
    }

    // --- Promotions ---
    public function promotions(MarketingFilterRequest $request, ListPromotionsAction $action): JsonResponse
    {
        $paginator = $action->execute(MarketingFilterDto::fromRequest($request), self::PER_PAGE);

        return self::successfulResponseWithData([
            'data' => PromotionResource::collection($paginator->items()),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function storePromotion(StorePromotionRequest $request, CreatePromotionAction $action): JsonResponse
    {
        $promotion = $action->execute(PromotionDto::fromRequest($request));

        return self::successfulResponseWithData($promotion);
    }

    public function updatePromotion(UpdatePromotionRequest $request, int $id, UpdatePromotionAction $action): JsonResponse
    {
        $promotion = $action->execute($id, PromotionDto::fromRequest($request));

        return self::successfulResponseWithData($promotion);
    }

    public function destroyPromotion(int $id, DeletePromotionAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse('Promotion deleted successfully.');
    }
}
