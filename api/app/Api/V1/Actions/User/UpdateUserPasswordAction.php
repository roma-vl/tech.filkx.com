<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordAction
{
    public function execute(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = $newPassword; // Casts will automatically hash it
        $user->save();

        $user->notify(new PasswordChangedNotification(request()->ip() ?? 'unknown', now()->toDateTimeString()));

        return true;
    }
}
