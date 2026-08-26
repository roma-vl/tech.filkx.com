<?php

namespace App\Api\V1\Actions\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetSupportTicketAction
{
    public function execute(SupportTicket $ticket, User $user): SupportTicket
    {
        if ($ticket->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $ticket->load(['publicMessages.user', 'user', 'product.variants']);
    }
}
