<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class UpdateUserLocaleAction
{
    public function execute(User $user, string $locale): User
    {
        $user->locale = $locale;
        $user->save();

        return $user;
    }
}
