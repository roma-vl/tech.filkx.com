<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteUserAvatarAction
{
    public function execute(User $user): User
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return $user;
    }
}
