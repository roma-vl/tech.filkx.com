<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Activity\GetUserActivitiesAction;
use App\Api\V1\Resources\Activity\UserActivityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ActivityController extends BaseApiController
{
    #[OA\Get(
        path: '/api/activities',
        summary: 'Get the authenticated user\'s activity log',
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        tags: [
            'Activity',
        ],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1,
                ),
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 15,
                ),
            ),
            new OA\Parameter(
                name: 'type',
                description: 'Filter by activity type (e.g. order.placed, order.status_changed, review.created, product.viewed)',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                ),
            ),
            new OA\Parameter(
                name: 'dateFrom',
                description: 'Start date for filtering (YYYY-MM-DD)',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date',
                ),
            ),
            new OA\Parameter(
                name: 'dateTo',
                description: 'End date for filtering (YYYY-MM-DD)',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    format: 'date',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User activities list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/UserActivityResource',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request, GetUserActivitiesAction $action): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['type', 'dateFrom', 'dateTo']);

        $activities = $action->execute($request->user(), $perPage, $filters);

        return self::successfulResponseWithData(
            UserActivityResource::collection($activities)
        );
    }
}
