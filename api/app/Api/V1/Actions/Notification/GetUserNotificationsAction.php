<?php

namespace App\Api\V1\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserNotificationsAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateForUser($user->id, $perPage);
    }
}
