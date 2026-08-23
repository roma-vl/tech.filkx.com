<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;
use App\Notifications\AccountRestoredNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RestoreDeletedAccountAction
{
    /**
     * @throws ModelNotFoundException
     */
    public function execute(int $userId): User
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();

        $user->notify(new AccountRestoredNotification(config('app.frontend_url').'/login'));

        return $user;
    }
}
