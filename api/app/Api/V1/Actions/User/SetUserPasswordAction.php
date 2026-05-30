<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class SetUserPasswordAction
{
    public function execute(User $user, string $password): User
    {
        $user->password = $password;
        $user->save();
        return $user;
    }
}
