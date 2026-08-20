<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class GetUserNotificationPreferencesAction
{
    private const DEFAULT_PREFERENCES = [
        'newsletter' => false,
        'product_updates' => true,
        'marketing_emails' => false,
    ];

    public function execute(User $user): array
    {
        return $user->notification_preferences ?? self::DEFAULT_PREFERENCES;
    }
}
