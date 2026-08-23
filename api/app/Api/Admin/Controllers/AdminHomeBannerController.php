<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\HomeBanner\CreateAdminHomeBannerAction;
use App\Api\Admin\Actions\HomeBanner\DeleteAdminHomeBannerAction;
use App\Api\Admin\Actions\HomeBanner\ListAdminHomeBannersAction;
use App\Api\Admin\Actions\HomeBanner\UpdateAdminHomeBannerAction;
use App\Api\Admin\Actions\HomeBanner\UploadHomeBannerImageAction;
use App\Api\Admin\Dto\HomeBannerDto;
use App\Api\Admin\Requests\HomeBannerRequest;
use App\Api\Admin\Requests\UploadHomeBannerImageRequest;
use App\Api\Admin\Resources\HomeBannerResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminHomeBannerController extends BaseApiController
{
    public function index(ListAdminHomeBannersAction $action): JsonResponse
    {
        $banners = $action->execute();

        return self::successfulResponseWithData(HomeBannerResource::collection($banners));
    }

    public function store(HomeBannerRequest $request, CreateAdminHomeBannerAction $action): JsonResponse
    {
        $banner = $action->execute(HomeBannerDto::fromRequest($request));

        return self::successfulResponseWithData(new HomeBannerResource($banner), Response::HTTP_CREATED);
    }

    public function update(HomeBannerRequest $request, int $id, UpdateAdminHomeBannerAction $action): JsonResponse
    {
        $banner = $action->execute($id, HomeBannerDto::fromRequest($request));

        return self::successfulResponseWithData(new HomeBannerResource($banner));
    }

    public function destroy(int $id, DeleteAdminHomeBannerAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }

    public function uploadImage(UploadHomeBannerImageRequest $request, UploadHomeBannerImageAction $action): JsonResponse
    {
        $result = $action->execute($request->file('image'));

        return self::successfulResponseWithData($result);
    }
}
