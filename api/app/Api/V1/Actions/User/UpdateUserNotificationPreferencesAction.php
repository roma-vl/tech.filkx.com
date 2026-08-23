<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class UpdateUserNotificationPreferencesAction
{
    public function execute(User $user, array $preferences): array
    {
        $user->notification_preferences = $preferences;
        $user->save();

        return $user->notification_preferences;
    }
}
