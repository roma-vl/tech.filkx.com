<?php

namespace App\Api\V1\Actions\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TransferSupportTicketAction
{
    public function execute(SupportTicket $ticket, User $user, string $handledBy): SupportTicket
    {
        if ($ticket->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $ticket->update(['handled_by' => $handledBy]);

        return $ticket->load('messages');
    }
}
