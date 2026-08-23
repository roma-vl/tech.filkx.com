<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Product\BulkDeleteAdminProductsAction;
use App\Api\Admin\Actions\Product\BulkUpdateProductCategoryAction;
use App\Api\Admin\Actions\Product\BulkUpdateProductStatusAction;
use App\Api\Admin\Actions\Product\CreateAdminProductAction;
use App\Api\Admin\Actions\Product\DeleteAdminProductAction;
use App\Api\Admin\Actions\Product\ListAdminProductsAction;
use App\Api\Admin\Actions\Product\ListTrashedAdminProductsAction;
use App\Api\Admin\Actions\Product\RestoreAdminProductAction;
use App\Api\Admin\Actions\Product\UpdateAdminProductAction;
use App\Api\Admin\Actions\Product\UploadProductImageAction;
use App\Api\Admin\Dto\ProductDto;
use App\Api\Admin\Requests\BulkDeleteProductsRequest;
use App\Api\Admin\Requests\BulkUpdateProductCategoryRequest;
use App\Api\Admin\Requests\BulkUpdateProductStatusRequest;
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

    public function bulkDestroy(BulkDeleteProductsRequest $request, BulkDeleteAdminProductsAction $action): JsonResponse
    {
        $count = $action->execute($request->input('ids'));

        return self::successfulResponseWithData(['deleted' => $count]);
    }

    public function bulkUpdateStatus(BulkUpdateProductStatusRequest $request, BulkUpdateProductStatusAction $action): JsonResponse
    {
        $count = $action->execute($request->input('ids'), $request->input('status'));

        return self::successfulResponseWithData(['updated' => $count]);
    }

    public function bulkUpdateCategory(BulkUpdateProductCategoryRequest $request, BulkUpdateProductCategoryAction $action): JsonResponse
    {
        $count = $action->execute($request->input('ids'), (int) $request->input('categoryId'));

        return self::successfulResponseWithData(['updated' => $count]);
    }

    public function trashed(ListTrashedAdminProductsAction $action): JsonResponse
    {
        $products = $action->execute();

        return self::successfulResponseWithData(AdminProductResource::collection($products));
    }

    public function restore(int $id, RestoreAdminProductAction $action): JsonResponse
    {
        $product = $action->execute($id);

        return self::successfulResponseWithData(new AdminProductResource($product));
    }
}
