<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Brand\CreateAdminBrandAction;
use App\Api\Admin\Actions\Brand\DeleteAdminBrandAction;
use App\Api\Admin\Actions\Brand\ListAdminBrandsAction;
use App\Api\Admin\Actions\Brand\UpdateAdminBrandAction;
use App\Api\Admin\Dto\BrandDto;
use App\Api\Admin\Requests\StoreBrandRequest;
use App\Api\Admin\Requests\UpdateBrandRequest;
use App\Api\Admin\Resources\BrandResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminBrandController extends BaseApiController
{
    public function index(ListAdminBrandsAction $action): JsonResponse
    {
        $brands = $action->execute();

        return self::successfulResponseWithData(BrandResource::collection($brands));
    }

    public function store(StoreBrandRequest $request, CreateAdminBrandAction $action): JsonResponse
    {
        $brand = $action->execute(BrandDto::fromRequest($request));

        return self::successfulResponseWithData(new BrandResource($brand), Response::HTTP_CREATED);
    }

    public function update(UpdateBrandRequest $request, int $id, UpdateAdminBrandAction $action): JsonResponse
    {
        $brand = $action->execute($id, BrandDto::fromRequest($request));

        return self::successfulResponseWithData(new BrandResource($brand));
    }

    public function destroy(int $id, DeleteAdminBrandAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }
}
