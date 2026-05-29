<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\System\GetSystemHealthAction;
use Illuminate\Http\JsonResponse;

class AdminSystemController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/system/health",
     *     summary="Get system health monitoring data",
     *     description="Returns real-time metrics for server (CPU, RAM, Disk), database status, and internal services.",
     *     tags={"Admin System"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="server", type="object",
     *                     @OA\Property(property="cpu", type="object"),
     *                     @OA\Property(property="ram", type="object"),
     *                     @OA\Property(property="disk", type="object"),
     *                     @OA\Property(property="uptime", type="string")
     *                 ),
     *                 @OA\Property(property="database", type="object"),
     *                 @OA\Property(property="services", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function health(GetSystemHealthAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute()->toArray());
    }
}
