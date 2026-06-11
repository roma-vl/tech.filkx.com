<?php

namespace App\Api\Admin\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteNotificationAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(int $id): void
    {
        $notification = $this->notificationRepository->find($id);

        if (! $notification) {
            throw new NotFoundHttpException('Notification not found.');
        }

        $this->notificationRepository->delete($notification);
    }
}
