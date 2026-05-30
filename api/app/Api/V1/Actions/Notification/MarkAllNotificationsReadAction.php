<?php

namespace App\Api\V1\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use App\Models\User;

class MarkAllNotificationsReadAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(User $user): void
    {
        $this->notificationRepository->markAllAsRead($user->id);
    }
}
