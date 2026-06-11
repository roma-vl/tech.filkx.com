<?php

namespace App\Api\Admin\Actions\AuditLog;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuditLogsAction
{
    /**
     * Get paginated logs with filters.
     */
    public function execute(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if (! empty($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['userId'])) {
            $query->where('user_id', $filters['userId']);
        }

        if (! empty($filters['search'])) {
            $query->where('message', 'like', '%'.$filters['search'].'%');
        }

        return $query->paginate($perPage);
    }
}
