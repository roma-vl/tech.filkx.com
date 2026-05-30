<?php

namespace App\Api\V1\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use App\Models\Notification;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MarkNotificationReadAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(User $user, int $id): Notification
    {
        $notification = $this->notificationRepository->find($id);

        if (! $notification) {
            throw new NotFoundHttpException('Notification not found.');
        }

        if ($notification->user_id && $notification->user_id !== $user->id) {
            throw new AccessDeniedHttpException('This notification does not belong to you.');
        }

        return $this->notificationRepository->markAsRead($notification);
    }
}
