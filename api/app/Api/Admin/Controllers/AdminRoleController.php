<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\CreatePermissionAction;
use App\Api\Admin\Actions\CreateRoleAction;
use App\Api\Admin\Actions\DeleteRoleAction;
use App\Api\Admin\Actions\ListPermissionsAction;
use App\Api\Admin\Actions\ListRolesAction;
use App\Api\Admin\Actions\UpdateRoleAction;
use App\Api\Admin\Requests\StorePermissionRequest;
use App\Api\Admin\Requests\StoreRoleRequest;
use App\Api\Admin\Requests\UpdateRoleRequest;
use App\Api\Admin\Resources\PermissionResource;
use App\Api\Admin\Resources\RoleResource;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(name="Admin Roles", description="Roles & Permissions management for administrators")
 */
class AdminRoleController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/roles",
     *     summary="List all roles",
     *     tags={"Admin Roles"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of roles",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Role"))
     *         )
     *     )
     * )
     */
    public function index(Request $request, ListRolesAction $action): JsonResponse
    {
        $roles = $action->execute($request->all());

        return self::successfulResponseWithData(RoleResource::collection($roles));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/roles",
     *     summary="Create a new role",
     *     tags={"Admin Roles"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="Moderator"),
     *             @OA\Property(property="description", type="string", example="Can manage comments"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"comment.delete", "comment.edit"})
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Role created successfully")
     * )
     */
    public function store(StoreRoleRequest $request, CreateRoleAction $action): JsonResponse
    {
        $role = $action->execute($request->validated());

        return self::successfulResponseWithData(new RoleResource($role), Response::HTTP_CREATED);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/roles/{id}",
     *     summary="Update a role",
     *     tags={"Admin Roles"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Role updated successfully")
     * )
     */
    public function update(UpdateRoleRequest $request, int $id, UpdateRoleAction $action): JsonResponse
    {
        $role = $action->execute($id, $request->validated());

        return self::successfulResponseWithData(new RoleResource($role));
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/roles/{id}",
     *     summary="Delete a role",
     *     tags={"Admin Roles"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Role deleted successfully")
     * )
     */
    public function destroy(int $id, DeleteRoleAction $action): JsonResponse
    {
        try {
            $action->execute($id);

            return self::successfulResponse();
        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/permissions",
     *     summary="List all available permissions",
     *     tags={"Admin Roles"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Permission"))
     *         )
     *     )
     * )
     */
    public function permissions(ListPermissionsAction $action): JsonResponse
    {
        $permissions = $action->execute();

        return self::successfulResponseWithData(PermissionResource::collection($permissions));
    }

    /**
     * @OA\Post(
     *     path="/api/admin/permissions",
     *     summary="Create a new permission",
     *     tags={"Admin Roles"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "slug", "resource", "action"},
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="resource", type="string"),
     *             @OA\Property(property="action", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Permission created successfully")
     * )
     */
    public function storePermission(StorePermissionRequest $request, CreatePermissionAction $action): JsonResponse
    {
        $permission = $action->execute($request->validated());

        return self::successfulResponseWithData(new PermissionResource($permission), Response::HTTP_CREATED);
    }
}
