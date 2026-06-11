<?php

namespace App\Api\Admin\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use App\Models\Notification;

class CreateNotificationAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(array $data): Notification
    {
        return $this->notificationRepository->create($data);
    }
}
