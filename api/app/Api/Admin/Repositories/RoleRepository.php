<?php

namespace App\Api\Admin\Repositories;

use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class RoleRepository
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Role::with('permissions')
            ->withCount('users')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'scope' => 'global',
        ]);

        if (isset($data['permissions'])) {
            $role->givePermissions($data['permissions']);
        }

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'] ?? $role->name,
            'slug' => isset($data['name']) ? Str::slug($data['name']) : $role->slug,
            'description' => $data['description'] ?? $role->description,
        ]);

        if (isset($data['permissions'])) {
            $role->permissions()->sync(
                Permission::whereIn('slug', $data['permissions'])->pluck('id')
            );
        }

        return $role;
    }

    public function delete(Role $role): bool
    {
        if ($role->is_system) {
            throw new Exception('Cannot delete system role');
        }

        return $role->delete();
    }
}
