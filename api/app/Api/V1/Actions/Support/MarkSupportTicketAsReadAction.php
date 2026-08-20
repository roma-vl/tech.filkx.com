<?php

namespace App\Api\V1\Actions\Support;

use App\Models\SupportTicket;
use App\Models\User;

class MarkSupportTicketAsReadAction
{
    public function execute(SupportTicket $ticket, User $user): void
    {
        if ($ticket->user_id !== $user->id) {
            abort(403);
        }

        $ticket->update(['read_at' => now()]);
        $ticket->messages()->where('is_admin', true)->whereNull('read_at')->update(['read_at' => now()]);
    }
}
