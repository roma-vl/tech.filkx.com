<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;

class UpdateSupportTicketStatusAction
{
    /**
     * Update the status of a support ticket.
     */
    public function execute(SupportTicket $ticket, string $status): SupportTicket
    {
        $updates = ['status' => $status];
        if ($ticket->handled_by === 'ai') {
            $updates['handled_by'] = 'human';
        }
        $ticket->update($updates);

        return $ticket;
    }
}
