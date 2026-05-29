<?php

namespace App\Api\Admin\Actions;

use App\Models\User;

class ToggleUserSuspensionAction
{
    public function execute(int $userId, string $ip, string $userAgent): User
    {
        $user = User::withTrashed()->findOrFail($userId);

        $newStatus = $user->status === 'suspended' ? 'active' : 'suspended';
        $user->update(['status' => $newStatus]);



        event(new \App\Events\AuditEvent(new \App\Api\V1\Dto\AuditLogDto(
            action: 'user.updated',
            domain: 'team',
            message: "Admin toggled suspension for user: {$user->name}. New status: ".($user->status === 'suspended' ? 'Suspended' : 'Active'),
            userId: \Illuminate\Support\Facades\Auth::id(),
            subjectType: User::class,
            subjectId: $user->id,
            payload: ['is_suspended' => $user->status === 'suspended'],
            ipAddress: $ip,
            userAgent: $userAgent
        )));

        return $user->fresh();
    }
}
