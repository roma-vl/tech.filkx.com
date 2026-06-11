<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Collection;

class ListSupportTicketMessagesAction
{
    /**
     * List messages for a support ticket with cursor pagination.
     *
     * @param  int|null  $beforeId  ID of the message to load messages before (older than)
     * @param  int  $limit  Number of messages to load
     */
    public function execute(SupportTicket $ticket, ?int $beforeId = null, int $limit = 5): Collection
    {
        return $ticket->messages()
            ->with('user')
            ->when($beforeId, function ($query) use ($beforeId) {
                $query->where('id', '<', $beforeId);
            })
            ->latest() // Order by created_at desc (newest first)
            ->take($limit)
            ->get()
            ->reverse() // Reverse to show oldest first in the chunk
            ->values();
    }
}
