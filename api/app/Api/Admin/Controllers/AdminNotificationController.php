<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Notification\CreateNotificationAction;
use App\Api\Admin\Actions\Notification\DeleteNotificationAction;
use App\Api\Admin\Actions\Notification\ListNotificationsAction;
use App\Api\Admin\Actions\Notification\BroadcastNotificationAction;
use App\Api\Admin\Requests\CreateNotificationRequest;
use App\Api\Admin\Requests\BroadcastNotificationRequest;
use App\Api\V1\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;

class AdminNotificationController extends BaseApiController
{
    public function index(ListNotificationsAction $action): JsonResponse
    {
        $notifications = $action->execute(self::PER_PAGE);

        return self::successfulResponseWithData([
            'data' => NotificationResource::collection($notifications->items()),
            'meta' => [
                'currentPage' => $notifications->currentPage(),
                'lastPage'    => $notifications->lastPage(),
                'perPage'     => $notifications->perPage(),
                'total'       => $notifications->total(),
            ]
        ]);
    }

    public function store(CreateNotificationRequest $request, CreateNotificationAction $action): JsonResponse
    {
        $notification = $action->execute($request->validated());

        return self::successfulResponseWithData(
            new NotificationResource($notification),
            201
        );
    }

    public function broadcast(BroadcastNotificationRequest $request, BroadcastNotificationAction $action): JsonResponse
    {
        $action->execute($request->validated());

        return self::successfulResponse('Notifications broadcasted successfully.');
    }

    public function destroy(int $id, DeleteNotificationAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse('Notification deleted successfully.');
    }
}
