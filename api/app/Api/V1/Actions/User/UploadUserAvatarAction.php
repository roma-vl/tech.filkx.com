<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadUserAvatarAction
{
    public function execute(User $user, UploadedFile $avatar): User
    {
        $path = $avatar->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();
        return $user;
    }
}
