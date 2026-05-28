<?php

namespace App\Api\Admin\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ListAdminUsersAction
{
    public function execute(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = User::query()->with(['roles']);

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage);
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $lowSearch = mb_strtolower($search);
                $q->where(DB::raw('LOWER(name)'), 'like', "%{$lowSearch}%")
                    ->orWhere(DB::raw('LOWER(email)'), 'like', "%{$lowSearch}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'deleted') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $status);
            }
        }

        if ((! empty($filters['with_deleted']) || ! empty($filters['withDeleted'])) && (! isset($filters['status']) || $filters['status'] !== 'deleted')) {
            $query->withTrashed();
        }

        $dateFrom = $filters['date_from'] ?? $filters['dateFrom'] ?? null;
        if (! empty($dateFrom)) {
            $query->where('created_at', '>=', $dateFrom);
        }

        $dateTo = $filters['date_to'] ?? $filters['dateTo'] ?? null;
        if (! empty($dateTo)) {
            $query->where('created_at', '<=', $dateTo.' 23:59:59');
        }
    }
}
