<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class RestoreDeletedAccountAction
{
    public function execute(User $user): void
    {
        $user->restore();
    }
}
