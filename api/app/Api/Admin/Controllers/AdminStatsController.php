<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\GetAdminStatsAction;
use App\Api\Admin\Requests\StatsRequest;
use App\Api\Admin\Resources\StatsResource;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/stats",
     *     summary="Get administrative statistics overview",
     *     description="Returns high-level statistics for users, streams, videos, and revenue, including trends and recent activity.",
     *     operationId="getAdminStats",
     *     tags={"Admin Settings"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="stats", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="label", type="string", example="Total Users"),
     *                         @OA\Property(property="value", type="string", example="1,234"),
     *                         @OA\Property(property="trend", type="number", format="float", example=5.2),
     *                         @OA\Property(property="icon", type="string", example="UsersIcon"),
     *                         @OA\Property(property="bgClass", type="string", example="bg-blue-500")
     *                     )
     *                 ),
     *                 @OA\Property(property="recentUsers", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="plan", type="string", example="Premium"),
     *                         @OA\Property(property="joined", type="string", example="2 hours ago")
     *                     )
     *                 ),
     *                 @OA\Property(property="unreadTickets", type="integer", example=3)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     )
     * )
     */
    public function index(StatsRequest $request, GetAdminStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return self::successfulResponseWithData(new StatsResource($stats));
    }
}
