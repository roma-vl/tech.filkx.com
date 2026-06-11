<?php

namespace App\Api\V1\Repositories;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function paginateAll(int $perPage): LengthAwarePaginator;

    public function paginateForUser(?int $userId, int $perPage): LengthAwarePaginator;

    public function create(array $data): Notification;

    public function markAsRead(Notification $notification): Notification;

    public function markAllAsRead(int $userId): void;

    public function find(int $id): ?Notification;

    public function delete(Notification $notification): bool;
}
