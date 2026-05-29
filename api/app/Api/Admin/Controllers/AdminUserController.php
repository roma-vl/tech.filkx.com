<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\AssignUserRoleAction;
use App\Api\Admin\Actions\CreateAdminUserAction;
use App\Api\Admin\Actions\DeleteAdminUserAction;
use App\Api\Admin\Actions\ExportAdminUsersAction;
use App\Api\Admin\Actions\ListAdminUsersAction;
use App\Api\Admin\Actions\ListSubscriptionPlanNamesAction;
use App\Api\Admin\Actions\ToggleUserSuspensionAction;
use App\Api\Admin\Actions\UpdateAdminUserAction;
use App\Api\Admin\Requests\StoreAdminUserRequest;
use App\Api\Admin\Requests\UpdateAdminUserRequest;
use App\Api\Admin\Resources\AdminUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(name="Admin Users", description="Direct client management for administrators")
 */
class AdminUserController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/users",
     *     summary="List all clients with advanced filtering",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="plan", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active", "trial", "suspended", "expired", "deleted"})),
     *     @OA\Parameter(name="dateFrom", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="dateTo", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="withDeleted", in="query", @OA\Schema(type="boolean")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of clients",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     )
     * )
     */
    public function index(Request $request, ListAdminUsersAction $action): JsonResponse
    {
        $users = $action->execute($request->all());
        $users->load('roles');

        return self::successfulResponseWithData(AdminUserResource::collection($users));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/users/plans",
     *     summary="List all available subscription plans",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of plan names",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function plans(ListSubscriptionPlanNamesAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    /**
     * @OA\Post(
     *     path="/api/admin/users",
     *     summary="Create a new client",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", minLength=8)
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="User created successfully")
     * )
     */
    public function store(StoreAdminUserRequest $request, CreateAdminUserAction $action): JsonResponse
    {
        $user = $action->execute(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return self::successfulResponseWithData(new AdminUserResource($user), Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/users/{id}",
     *     summary="Update client details and subscription overrides",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="status", type="string", enum={"active", "suspended"}),
     *             @OA\Property(property="featuresSnapshot", type="object", description="JSON override for subscription features"),
     *             @OA\Property(property="subscriptionUsage", type="object", description="JSON override for usage info"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="User updated successfully")
     * )
     */
    public function update(
        UpdateAdminUserRequest $request,
        int $id,
        UpdateAdminUserAction $action,
        AssignUserRoleAction $assignRoleAction
    ): JsonResponse {
        $data = $request->validated();
        $user = $action->execute($id, $data, $request->ip(), $request->userAgent());

        if (isset($data['roles'])) {
            $assignRoleAction->execute($user, $data['roles']);
        }

        return self::successfulResponseWithData(new AdminUserResource($user->load('roles')));
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/users/{id}",
     *     summary="Soft delete a client",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="User deleted successfully")
     * )
     */
    public function destroy(int $id, Request $request, DeleteAdminUserAction $action): JsonResponse
    {
        try {
            $action->execute($id, $request->ip(), $request->userAgent());

            return self::successfulResponse();
        } catch (\Exception $e) {
            return self::errorResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/users/{id}/suspend",
     *     summary="Toggle client suspension",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="User status toggled successfully")
     * )
     */
    public function suspend(int $id, Request $request, ToggleUserSuspensionAction $action): JsonResponse
    {
        $user = $action->execute($id, $request->ip(), $request->userAgent());

        return self::successfulResponseWithData(new AdminUserResource($user));
    }

    /**
     * @OA\Get(
     *     path="/api/admin/users/export",
     *     summary="Export clients to CSV with filters applied",
     *     tags={"Admin Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="plan", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="dateFrom", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="dateTo", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="withDeleted", in="query", @OA\Schema(type="boolean")),
     *
     *     @OA\Response(response=200, description="CSV file download")
     * )
     */
    public function export(Request $request, ExportAdminUsersAction $action): Response
    {
        return $action->execute($request->all());
    }
}
