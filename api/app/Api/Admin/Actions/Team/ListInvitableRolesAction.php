<?php

namespace App\Api\Admin\Actions\Team;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class ListInvitableRolesAction
{
    /**
     * @return Collection<int, Role>
     */
    public function execute(): Collection
    {
        return Role::orderBy('name')->get();
    }
}
