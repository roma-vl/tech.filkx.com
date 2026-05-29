<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListSupportTicketsAction
{
    /**
     * List support tickets with optional filters and search.
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = SupportTicket::with(['user', 'lastMessage'])
            ->withCount(['unreadMessagesForAdmin as unreadCount']);

        // Search by ID, Subject, Username, Email
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filter by Status
        if (! empty($filters['status']) && $filters['status'] !== 'All') {
            $query->where('status', strtolower($filters['status']));
        } else {
            // Exclude deleted tickets by default when showing "All"
            $query->where('status', '!=', 'deleted');
        }

        // Filter by Tag
        if (! empty($filters['tag'])) {
            $query->whereJsonContains('tags', $filters['tag']);
        }

        // Filter by User
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest('updated_at')->paginate(20);
    }
}
