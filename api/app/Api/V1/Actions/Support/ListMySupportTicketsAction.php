<?php

namespace App\Api\V1\Actions\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListMySupportTicketsAction
{
    public function execute(User $user): Collection
    {
        return SupportTicket::where('user_id', $user->id)
            ->with(['lastMessage', 'product'])
            ->withCount(['unreadMessagesForUser as unreadCount'])
            ->latest('updated_at')
            ->get();
    }
}
