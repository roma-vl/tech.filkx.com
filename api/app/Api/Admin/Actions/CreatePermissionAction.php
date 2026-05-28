<?php

namespace App\Api\Admin\Actions;

use App\Models\Permission;

class CreatePermissionAction
{
    public function execute(array $data): Permission
    {
        return Permission::create($data);
    }
}
