<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class ConfirmEmailChangeAction
{
    /**
     * Applies a pending email change confirmed via signed link.
     *
     * Returns false (without making any change) if the new address was taken by
     * another account since the change was requested - a rare race condition the
     * caller must surface to the user rather than silently overwriting.
     */
    public function execute(int $userId, string $newEmail): bool
    {
        $user = User::findOrFail($userId);

        if (User::where('email', $newEmail)->exists()) {
            return false;
        }

        $user->email = $newEmail;
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return true;
    }
}
