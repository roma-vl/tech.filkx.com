<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class SystemController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/system/status",
     *     summary="Get public system status",
     *     tags={"System"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="System status",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="maintenanceMode", type="boolean", example=false),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function status(): JsonResponse
    {
        return self::successfulResponseWithData([
            'maintenance_mode' => app()->isDownForMaintenance(),
            'version' => config('app.version', '1.0.0'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
