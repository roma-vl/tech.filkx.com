<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;

class UpdateSupportTicketTagsAction
{
    public function execute(SupportTicket $ticket, array $tags): SupportTicket
    {
        $ticket->update(['tags' => $tags]);

        return $ticket;
    }
}
