<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Notification\GetUserNotificationsAction;
use App\Api\V1\Actions\Notification\MarkAllNotificationsReadAction;
use App\Api\V1\Actions\Notification\MarkNotificationReadAction;
use App\Api\V1\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class NotificationController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get user notifications",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User notifications",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/NotificationResource")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request, GetUserNotificationsAction $action): JsonResponse
    {
        $notifications = $action->execute($request->user());

        return self::successfulResponseWithData(
            NotificationResource::collection($notifications)
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/notifications/{id}/read",
     *     summary="Mark notification as read",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read",
     *
     *         @OA\JsonContent(ref="#/components/schemas/NotificationResource")
     *     )
     * )
     */
    public function markRead(Request $request, string $id, MarkNotificationReadAction $action): JsonResponse
    {
        $notification = $action->execute($request->user(), $id);

        return self::successfulResponseWithData(new NotificationResource($notification));
    }

    /**
     * @OA\Patch(
     *     path="/api/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All notifications marked as read")
     *         )
     *     )
     * )
     */
    public function markAllRead(Request $request, MarkAllNotificationsReadAction $action): JsonResponse
    {
        $action->execute($request->user());

        return self::successfulResponse('All notifications marked as read');
    }
}
