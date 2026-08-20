<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\AssignUserRoleAction;
use App\Api\Admin\Actions\CreateAdminUserAction;
use App\Api\Admin\Actions\Team\ListInvitableRolesAction;
use App\Api\Admin\Actions\Team\ListTeamMembersAction;
use App\Api\Admin\Actions\ToggleUserSuspensionAction;
use App\Api\Admin\Resources\AdminUserResource;
use App\Api\Admin\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class AdminTeamController extends BaseApiController
{
    #[OA\Get(
        path: '/api/admin/team',
        summary: 'List all admin team members and summary stats',
        security: [['bearerAuth' => []]],
        tags: ['Admin Team'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'team',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                                new OA\Property(
                                    property: 'stats',
                                    properties: [
                                        new OA\Property(property: 'total', type: 'integer', example: 5),
                                        new OA\Property(property: 'owners', type: 'integer', example: 1),
                                    ],
                                    type: 'object',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(ListTeamMembersAction $action): JsonResponse
    {
        $result = $action->execute();

        return self::successfulResponseWithData([
            'team' => AdminUserResource::collection($result['team']),
            'stats' => $result['stats'],
        ]);
    }

    #[OA\Get(
        path: '/api/admin/team/roles',
        summary: 'List roles available for team invite',
        security: [['bearerAuth' => []]],
        tags: ['Admin Team'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'roles',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function roles(ListInvitableRolesAction $action): JsonResponse
    {
        $roles = $action->execute();

        return self::successfulResponseWithData([
            'roles' => RoleResource::collection($roles),
        ]);
    }

    #[OA\Post(
        path: '/api/admin/team/invite',
        summary: 'Invite a new admin team member',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'roleId'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
                    new OA\Property(property: 'roleId', type: 'integer', example: 2),
                ],
            ),
        ),
        security: [['bearerAuth' => []]],
        tags: ['Admin Team'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Team member invited successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function invite(
        Request $request,
        CreateAdminUserAction $createAction,
        AssignUserRoleAction $assignRoleAction
    ): JsonResponse {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'roleId' => ['required', 'exists:roles,id'],
        ]);

        $password = Str::random(12);

        $user = $createAction->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
        ], $request->ip(), $request->userAgent());

        $role = Role::findOrFail($data['roleId']);
        $assignRoleAction->execute($user, [$role->slug]);

        return self::successfulResponseWithData(new AdminUserResource($user->load('roles')));
    }

    #[OA\Post(
        path: '/api/admin/team/{id}/toggle-status',
        summary: 'Toggle admin team member active/suspended status',
        security: [['bearerAuth' => []]],
        tags: ['Admin Team'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status toggled successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', enum: ['active', 'suspended']),
                                new OA\Property(property: 'message', type: 'string', example: 'Team member activated successfully.'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Team member not found'),
        ],
    )]
    public function toggleStatus(int $id, Request $request, ToggleUserSuspensionAction $action): JsonResponse
    {
        $user = $action->execute($id, $request->ip(), $request->userAgent());

        return self::successfulResponseWithData([
            'status' => $user->status,
            'message' => $user->status === 'active'
                ? 'Team member activated successfully.'
                : 'Team member suspended successfully.',
        ]);
    }
}
