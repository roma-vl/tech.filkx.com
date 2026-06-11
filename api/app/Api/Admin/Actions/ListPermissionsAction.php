<?php

namespace App\Api\Admin\Actions;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class ListPermissionsAction
{
    /**
     * List all available permissions.
     */
    public function execute(): Collection
    {
        return Permission::all();
    }
}
