<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Delivery\GetDeliveryAvailabilityAction;
use App\Api\V1\Actions\Delivery\GetDeliveryDateEstimateAction;
use App\Api\V1\Actions\Delivery\SearchDeliveryCitiesAction;
use App\Api\V1\Actions\Delivery\SearchDeliveryWarehousesAction;
use App\Api\V1\Exceptions\DeliveryProviderUnavailableException;
use App\Api\V1\Requests\GetDeliveryDateEstimateRequest;
use App\Api\V1\Requests\SearchDeliveryCitiesRequest;
use App\Api\V1\Requests\SearchDeliveryWarehousesRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class DeliveryController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/delivery/availability',
        summary: 'Check whether the Nova Poshta delivery integration is configured',
        tags: [
            'Delivery',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Availability status',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'available', type: 'boolean'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function availability(GetDeliveryAvailabilityAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/v1/delivery/cities',
        summary: 'Search Nova Poshta cities/settlements by name',
        tags: [
            'Delivery',
        ],
        parameters: [
            new OA\Parameter(
                name: 'query',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matching cities',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'ref', type: 'string'),
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'area', type: 'string'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 503,
                description: 'Nova Poshta is not configured or unreachable',
            ),
        ],
    )]
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

    #[OA\Get(
        path: '/api/v1/delivery/warehouses',
        summary: 'Search Nova Poshta warehouses/postomats within a city',
        tags: [
            'Delivery',
        ],
        parameters: [
            new OA\Parameter(
                name: 'cityRef',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'query',
                description: 'Optional warehouse name/number filter',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matching warehouses',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'ref', type: 'string'),
                                    new OA\Property(property: 'number', type: 'string'),
                                    new OA\Property(property: 'description', type: 'string'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 503,
                description: 'Nova Poshta is not configured or unreachable',
            ),
        ],
    )]
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

    #[OA\Get(
        path: '/api/v1/delivery/estimate',
        summary: 'Estimate the delivery date to a Nova Poshta recipient city',
        description: 'Always returns 200. When the integration is not configured, the shipping-origin '
            .'city is not set, or Nova Poshta is unreachable, `available` is false rather than an error - '
            .'callers should fall back to a generic delivery message in that case.',
        tags: [
            'Delivery',
        ],
        parameters: [
            new OA\Parameter(
                name: 'cityRef',
                description: 'Recipient city ref, as returned by /delivery/cities',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estimate result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'available', type: 'boolean'),
                                new OA\Property(property: 'date', type: 'string', format: 'date', nullable: true),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function estimate(GetDeliveryDateEstimateRequest $request, GetDeliveryDateEstimateAction $action): JsonResponse
    {
        return self::successfulResponseWithData(
            $action->execute($request->validated('cityRef'))
        );
    }
}
