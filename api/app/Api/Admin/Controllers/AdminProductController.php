<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Product\CreateAdminProductAction;
use App\Api\Admin\Actions\Product\DeleteAdminProductAction;
use App\Api\Admin\Actions\Product\ListAdminProductsAction;
use App\Api\Admin\Actions\Product\UpdateAdminProductAction;
use App\Api\Admin\Actions\Product\UploadProductImageAction;
use App\Api\Admin\Dto\ProductDto;
use App\Api\Admin\Requests\StoreProductRequest;
use App\Api\Admin\Requests\UpdateProductRequest;
use App\Api\Admin\Requests\UploadProductImageRequest;
use App\Api\Admin\Resources\AdminProductResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminProductController extends BaseApiController
{
    public function index(ListAdminProductsAction $action): JsonResponse
    {
        $products = $action->execute();

        return self::successfulResponseWithData(AdminProductResource::collection($products));
    }

    public function store(StoreProductRequest $request, CreateAdminProductAction $action): JsonResponse
    {
        $product = $action->execute(ProductDto::fromRequest($request));

        return self::successfulResponseWithData(['id' => $product->id], Response::HTTP_CREATED);
    }

    public function update(UpdateProductRequest $request, int $id, UpdateAdminProductAction $action): JsonResponse
    {
        $product = $action->execute($id, ProductDto::fromRequest($request));

        return self::successfulResponseWithData(['id' => $product->id]);
    }

    public function destroy(int $id, DeleteAdminProductAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }

    public function uploadImage(UploadProductImageRequest $request, UploadProductImageAction $action): JsonResponse
    {
        $result = $action->execute($request->file('image'));

        return self::successfulResponseWithData($result);
    }
}
