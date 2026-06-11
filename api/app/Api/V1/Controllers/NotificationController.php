<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Notification\GetUserNotificationsAction;
use App\Api\V1\Actions\Notification\MarkAllNotificationsReadAction;
use App\Api\V1\Actions\Notification\MarkNotificationReadAction;
use App\Api\V1\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    public function index(Request $request, GetUserNotificationsAction $action): JsonResponse
    {
        $notifications = $action->execute($request->user());

        return self::successfulResponseWithData(
            NotificationResource::collection($notifications)
        );
    }

    public function markRead(Request $request, int $id, MarkNotificationReadAction $action): JsonResponse
    {
        $notification = $action->execute($request->user(), $id);

        return self::successfulResponseWithData(new NotificationResource($notification));
    }

    public function markAllRead(Request $request, MarkAllNotificationsReadAction $action): JsonResponse
    {
        $action->execute($request->user());

        return self::successfulResponse('All notifications marked as read');
    }
}
