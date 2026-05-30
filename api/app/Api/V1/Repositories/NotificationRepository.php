<?php

namespace App\Api\V1\Repositories;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function paginateAll(int $perPage): LengthAwarePaginator
    {
        return Notification::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateForUser(?int $userId, int $perPage): LengthAwarePaginator
    {
        $query = Notification::query();

        if ($userId) {
            // Show notifications meant for this user OR global ones
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereNull('user_id');
            });
        } else {
            // Unauthenticated/guest: only global ones
            $query->whereNull('user_id');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->update(['read_at' => now()]);
        return $notification;
    }

    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function find(int $id): ?Notification
    {
        return Notification::find($id);
    }

    public function delete(Notification $notification): bool
    {
        return (bool) $notification->delete();
    }
}
