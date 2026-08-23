<?php

namespace App\Api\Admin\Actions\Team;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListTeamMembersAction
{
    /**
     * @return array{team: Collection<int, User>, stats: array{total: int, owners: int}}
     */
    public function execute(): array
    {
        $adminRoles = Role::whereIn('slug', ['admin', 'administrator', 'moderator', 'support', 'owner'])
            ->pluck('id');

        $team = User::whereHas('roles', fn ($query) => $query->whereIn('roles.id', $adminRoles))
            ->with('roles')
            ->latest()
            ->get();

        return [
            'team' => $team,
            'stats' => [
                'total' => $team->count(),
                'owners' => $team->filter(fn (User $user) => $user->roles->contains('slug', 'owner'))->count(),
            ],
        ];
    }
}
