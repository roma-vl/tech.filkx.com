<?php

namespace App\Api\Admin\Actions;

use App\Models\Role;
use App\Models\User;

class AssignUserRoleAction
{
    public function execute(User $user, array $roleSlugs): void
    {
        $roles = Role::whereIn('slug', $roleSlugs)->pluck('id');
        $user->roles()->sync($roles);

        cache()->forget("user.{$user->id}.permissions");
    }
}
