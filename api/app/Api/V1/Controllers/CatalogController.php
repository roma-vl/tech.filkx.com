<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\ListCategoriesAction;
use App\Api\V1\Actions\ListBrandsAction;
use App\Api\V1\Actions\GetCatalogFiltersAction;
use App\Api\V1\Actions\ListProductsAction;
use App\Api\V1\Actions\GetProductDetailsAction;
use App\Api\V1\Requests\ListProductsRequest;
use Illuminate\Http\JsonResponse;

class CatalogController extends BaseApiController
{
    public function categories(ListCategoriesAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    public function brands(ListBrandsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    public function filters(GetCatalogFiltersAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    public function products(ListProductsRequest $request, ListProductsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->validated()));
    }

    public function product(string $slug, GetProductDetailsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($slug));
    }
}
