<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\PromoPage\CreateAdminPromoPageAction;
use App\Api\Admin\Actions\PromoPage\DeleteAdminPromoPageAction;
use App\Api\Admin\Actions\PromoPage\ListAdminPromoPagesAction;
use App\Api\Admin\Actions\PromoPage\UpdateAdminPromoPageAction;
use App\Api\Admin\Actions\PromoPage\UploadPromoPageImageAction;
use App\Api\Admin\Dto\PromoPageDto;
use App\Api\Admin\Requests\PromoPageRequest;
use App\Api\Admin\Requests\UploadPromoPageImageRequest;
use App\Api\Admin\Resources\PromoPageResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminPromoPageController extends BaseApiController
{
    public function index(ListAdminPromoPagesAction $action): JsonResponse
    {
        $promoPages = $action->execute();

        return self::successfulResponseWithData(PromoPageResource::collection($promoPages));
    }

    public function store(PromoPageRequest $request, CreateAdminPromoPageAction $action): JsonResponse
    {
        $promoPage = $action->execute(PromoPageDto::fromRequest($request));

        return self::successfulResponseWithData(new PromoPageResource($promoPage), Response::HTTP_CREATED);
    }

    public function update(PromoPageRequest $request, int $id, UpdateAdminPromoPageAction $action): JsonResponse
    {
        $promoPage = $action->execute($id, PromoPageDto::fromRequest($request));

        return self::successfulResponseWithData(new PromoPageResource($promoPage));
    }

    public function destroy(int $id, DeleteAdminPromoPageAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }

    public function uploadImage(UploadPromoPageImageRequest $request, UploadPromoPageImageAction $action): JsonResponse
    {
        $result = $action->execute($request->file('image'));

        return self::successfulResponseWithData($result);
    }
}
