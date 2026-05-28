<?php

namespace App\Api\Admin\Actions;

use App\Api\Admin\Repositories\RoleRepository;
use App\Models\Role;

class UpdateRoleAction
{
    protected RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): Role
    {
        $role = $this->repository->findById($id);

        return $this->repository->update($role, $data);
    }
}
