<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Delivery\GetDeliveryAvailabilityAction;
use App\Api\V1\Actions\Delivery\SearchDeliveryCitiesAction;
use App\Api\V1\Actions\Delivery\SearchDeliveryWarehousesAction;
use App\Api\V1\Exceptions\DeliveryProviderUnavailableException;
use App\Api\V1\Requests\SearchDeliveryCitiesRequest;
use App\Api\V1\Requests\SearchDeliveryWarehousesRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeliveryController extends BaseApiController
{
    public function availability(GetDeliveryAvailabilityAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    public function cities(SearchDeliveryCitiesRequest $request, SearchDeliveryCitiesAction $action): JsonResponse
    {
        try {
            return self::successfulResponseWithData(
                $action->execute($request->validated('query'))
            );
        } catch (DeliveryProviderUnavailableException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    public function warehouses(SearchDeliveryWarehousesRequest $request, SearchDeliveryWarehousesAction $action): JsonResponse
    {
        try {
            return self::successfulResponseWithData(
                $action->execute($request->validated('cityRef'), $request->validated('query'))
            );
        } catch (DeliveryProviderUnavailableException $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
