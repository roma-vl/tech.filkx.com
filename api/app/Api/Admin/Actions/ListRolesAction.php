<?php

namespace App\Api\Admin\Actions;

use App\Api\Admin\Repositories\RoleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ListRolesAction
{
    protected RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($filters['per_page'] ?? 15);
    }
}
