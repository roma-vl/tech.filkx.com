<?php

namespace App\Api\Admin\Actions;

use App\Api\Admin\Repositories\RoleRepository;
use App\Models\Role;

class CreateRoleAction
{
    protected RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): Role
    {
        return $this->repository->create($data);
    }
}
