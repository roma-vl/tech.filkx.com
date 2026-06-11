<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;

class MarkSupportMessagesReadAction
{
    /**
     * Mark specific messages as read.
     *
     * @return int Number of messages updated
     */
    public function execute(SupportTicket $ticket, array $messageIds): int
    {
        if (empty($messageIds)) {
            return 0;
        }

        $updated = $ticket->messages()
            ->whereIn('id', $messageIds)
            ->where('is_admin', false) // Only mark user messages as read by admin
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Only touch ticket if messages were actually updated (prevents jumping)
        if ($updated > 0) {
            $ticket->touch();
        }

        return $updated;
    }
}
