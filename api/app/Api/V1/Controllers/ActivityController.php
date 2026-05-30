<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Activity\GetUserActivitiesAction;
use App\Api\V1\Resources\Activity\UserActivityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ActivityController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/activities",
     *     summary="Get user activities",
     *     tags={"Activity"},
     *     security={{"passport": {}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Activity type filter (e.g. video, playlist, stream)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="dateFrom",
     *         in="query",
     *         description="Start date for filtering (YYYY-MM-DD)",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="dateTo",
     *         in="query",
     *         description="End date for filtering (YYYY-MM-DD)",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User activities list",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/UserActivityResource")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request, GetUserActivitiesAction $action): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['type', 'dateFrom', 'dateTo']);

        $activities = $action->execute($request->user(), $perPage, $filters);

        return self::successfulResponseWithData(
            UserActivityResource::collection($activities)
        );
    }
}
