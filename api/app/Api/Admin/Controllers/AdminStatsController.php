<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\GetAdminStatsAction;
use App\Api\Admin\Actions\Stats\GetAdminChartStatsAction;
use App\Api\Admin\Actions\Stats\GetAdminDistributionStatsAction;
use App\Api\Admin\Actions\Stats\GetAdminOverviewStatsAction;
use App\Api\Admin\Requests\StatsRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AdminStatsController extends BaseApiController
{
    #[OA\Get(
        path: '/api/admin/stats',
        summary: 'Get administrative statistics overview',
        description: 'Returns high-level statistics for users, streams, videos, and revenue, including trends and recent activity.',
        security: [['bearerAuth' => []]],
        tags: ['Admin Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'stats',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                                new OA\Property(
                                    property: 'recentUsers',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                                new OA\Property(property: 'unreadTickets', type: 'integer', example: 3),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ],
                ),
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.'),
                    ],
                ),
            ),
        ],
    )]
    public function index(StatsRequest $request, GetAdminStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return self::successfulResponseWithData($stats);
    }

    #[OA\Get(
        path: '/api/admin/analytics/overview',
        summary: 'Get store overview KPIs',
        security: [['bearerAuth' => []]],
        tags: ['Admin Stats'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'overview',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function overview(GetAdminOverviewStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/admin/analytics/charts',
        summary: 'Get weekly revenue/orders/signups chart series',
        security: [['bearerAuth' => []]],
        tags: ['Admin Stats'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'labels',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                ),
                                new OA\Property(
                                    property: 'datasets',
                                    properties: [
                                        new OA\Property(property: 'revenue', type: 'array', items: new OA\Items(type: 'number')),
                                        new OA\Property(property: 'streams', type: 'array', items: new OA\Items(type: 'number')),
                                        new OA\Property(property: 'signups', type: 'array', items: new OA\Items(type: 'number')),
                                    ],
                                    type: 'object',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function charts(GetAdminChartStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/admin/analytics/distributions',
        summary: 'Get category and order-status distribution breakdowns',
        security: [['bearerAuth' => []]],
        tags: ['Admin Stats'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'plans',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                                new OA\Property(
                                    property: 'content',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function distributions(GetAdminDistributionStatsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }
}
