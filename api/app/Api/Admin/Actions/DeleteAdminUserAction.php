<?php

namespace App\Api\Admin\Actions;

use App\Api\V1\Dto\AuditLogDto;
use App\Events\AuditEvent;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class DeleteAdminUserAction
{
    /**
     * Delete a user if they are not an admin.
     *
     * @throws Exception
     */
    public function execute(int $userId, string $ip, string $userAgent): void
    {
        $user = User::findOrFail($userId);

        if ($user->hasAnyRole(['admin'])) {
            throw new Exception('Cannot delete admin user');
        }

        $user->delete();

        event(new AuditEvent(new AuditLogDto(
            action: 'user.deleted',
            domain: 'team',
            message: "Admin deleted user: {$user->name} ({$user->email})",
            userId: Auth::id(),
            subjectType: User::class,
            subjectId: (string) $userId,
            payload: ['email' => $user->email],
            ipAddress: $ip,
            userAgent: $userAgent
        )));
    }
}
