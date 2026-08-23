<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;
use App\Notifications\AccountDeletionScheduledNotification;
use Illuminate\Support\Facades\URL;

class InitiateAccountDeletionAction
{
    private const RESTORE_WINDOW_DAYS = 3;

    public function execute(User $user): void
    {
        $restoreUrl = URL::temporarySignedRoute(
            'user.restore',
            now()->addDays(self::RESTORE_WINDOW_DAYS),
            ['userId' => $user->id]
        );

        $user->delete();

        $user->notify(new AccountDeletionScheduledNotification($restoreUrl));
    }
}
