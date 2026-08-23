<?php

namespace App\Api\V1\Actions\Activity;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserActivitiesAction
{
    public function execute(User $user, int $perPage, array $filters = []): LengthAwarePaginator
    {
        return UserActivity::query()
            ->where('user_id', $user->id)
            ->when(
                ! empty($filters['type']),
                fn ($query) => $query->where('activity_type', $filters['type'])
            )
            ->when(
                ! empty($filters['dateFrom']),
                fn ($query) => $query->whereDate('created_at', '>=', $filters['dateFrom'])
            )
            ->when(
                ! empty($filters['dateTo']),
                fn ($query) => $query->whereDate('created_at', '<=', $filters['dateTo'])
            )
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
