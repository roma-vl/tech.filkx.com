<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;

class MarkSupportTicketAsReadAction
{
    /**
     * Mark all unread messages for admin in a ticket as read.
     */
    public function execute(SupportTicket $ticket): void
    {
        $updated = $ticket->unreadMessagesForAdmin()->update(['read_at' => now()]);

        if ($updated > 0) {
            $ticket->touch();
        }
    }
}
