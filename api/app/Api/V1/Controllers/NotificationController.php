<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Notification\GetUserNotificationsAction;
use App\Api\V1\Actions\Notification\MarkAllNotificationsReadAction;
use App\Api\V1\Actions\Notification\MarkNotificationReadAction;
use App\Api\V1\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends BaseApiController
{
    #[OA\Get(
        path: '/api/notifications',
        summary: 'Get the authenticated user\'s notifications (own + broadcast), newest first',
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        tags: [
            'Notifications',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/NotificationResource',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request, GetUserNotificationsAction $action): JsonResponse
    {
        $notifications = $action->execute($request->user());

        return self::successfulResponseWithData(
            NotificationResource::collection($notifications)
        );
    }

    #[OA\Post(
        path: '/api/notifications/{id}/read',
        summary: 'Mark a single notification as read',
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        tags: [
            'Notifications',
        ],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/NotificationResource'),
                    ],
                ),
            ),
            new OA\Response(
                response: 403,
                description: 'The notification belongs to another user',
            ),
            new OA\Response(
                response: 404,
                description: 'Notification not found',
            ),
        ],
    )]
    public function markRead(Request $request, int $id, MarkNotificationReadAction $action): JsonResponse
    {
        $notification = $action->execute($request->user(), $id);

        return self::successfulResponseWithData(new NotificationResource($notification));
    }

    #[OA\Post(
        path: '/api/notifications/mark-all-read',
        summary: 'Mark all of the authenticated user\'s notifications as read',
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        tags: [
            'Notifications',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'All notifications marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'All notifications marked as read'),
                    ],
                ),
            ),
        ],
    )]
    public function markAllRead(Request $request, MarkAllNotificationsReadAction $action): JsonResponse
    {
        $action->execute($request->user());

        return self::successfulResponse('All notifications marked as read');
    }
}
