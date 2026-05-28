<?php

namespace App\Api\Admin\Actions;

use App\Api\Admin\Repositories\RoleRepository;

class DeleteRoleAction
{
    protected RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        $role = $this->repository->findById($id);

        return $this->repository->delete($role);
    }
}
