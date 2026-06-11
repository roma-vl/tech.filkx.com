<?php

namespace App\Api\Admin\Actions;

use App\Api\V1\Dto\AuditLogDto;
use App\Events\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ToggleUserSuspensionAction
{
    public function execute(int $userId, string $ip, string $userAgent): User
    {
        $user = User::withTrashed()->findOrFail($userId);

        $newStatus = $user->status === 'suspended' ? 'active' : 'suspended';
        $user->update(['status' => $newStatus]);

        event(new AuditEvent(new AuditLogDto(
            action: 'user.updated',
            domain: 'team',
            message: "Admin toggled suspension for user: {$user->name}. New status: ".($user->status === 'suspended' ? 'Suspended' : 'Active'),
            userId: Auth::id(),
            subjectType: User::class,
            subjectId: $user->id,
            payload: ['is_suspended' => $user->status === 'suspended'],
            ipAddress: $ip,
            userAgent: $userAgent
        )));

        return $user->fresh();
    }
}
