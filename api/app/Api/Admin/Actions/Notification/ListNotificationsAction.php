<?php

namespace App\Api\Admin\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListNotificationsAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateAll($perPage);
    }
}
