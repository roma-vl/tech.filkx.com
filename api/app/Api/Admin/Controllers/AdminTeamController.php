<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\AssignUserRoleAction;
use App\Api\Admin\Actions\CreateAdminUserAction;
use App\Api\Admin\Resources\AdminUserResource;
use App\Api\Admin\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(name="Admin Team", description="Admin team management")
 */
class AdminTeamController extends BaseApiController
{
    /**
     * List all admin team members and summary stats.
     *
     * GET /admin/team
     */
    public function index(): JsonResponse
    {
        $adminRoles = Role::whereIn('name', ['admin', 'administrator', 'moderator', 'support', 'owner'])
            ->pluck('id');

        $team = User::whereHas('roles', fn ($q) => $q->whereIn('roles.id', $adminRoles))
            ->with('roles')
            ->latest()
            ->get();

        $stats = [
            'total'  => $team->count(),
            'owners' => $team->filter(fn ($u) => $u->roles->contains('name', 'owner'))->count(),
        ];

        return self::successfulResponseWithData([
            'team'  => AdminUserResource::collection($team),
            'stats' => $stats,
        ]);
    }

    /**
     * List roles available for team invite.
     *
     * GET /admin/team/roles
     */
    public function roles(): JsonResponse
    {
        $roles = Role::orderBy('name')->get();

        return self::successfulResponseWithData([
            'roles' => RoleResource::collection($roles),
        ]);
    }

    /**
     * Invite a new admin team member.
     *
     * POST /admin/team/invite
     */
    public function invite(
        Request $request,
        CreateAdminUserAction $createAction,
        AssignUserRoleAction $assignRoleAction
    ): JsonResponse {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'unique:users,email'],
            'roleId' => ['required', 'exists:roles,id'],
        ]);

        $password = \Illuminate\Support\Str::random(12);

        $user = $createAction->execute([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $password,
        ], $request->ip(), $request->userAgent());

        $role = Role::findOrFail($data['roleId']);
        $assignRoleAction->execute($user, [$role->name]);

        return self::successfulResponseWithData(new AdminUserResource($user->load('roles')));
    }

    /**
     * Toggle admin team member active/suspended status.
     *
     * POST /admin/team/{id}/toggle-status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $user->status = $user->status === 'active' ? 'suspended' : 'active';
        $user->save();

        return self::successfulResponseWithData([
            'status'  => $user->status,
            'message' => $user->status === 'active'
                ? 'Team member activated successfully.'
                : 'Team member suspended successfully.',
        ]);
    }
}
