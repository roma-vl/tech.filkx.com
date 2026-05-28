<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\ServerLog\ClearServerLogAction;
use App\Api\Admin\Actions\ServerLog\ListServerLogsAction;
use App\Api\Admin\Actions\ServerLog\ShowServerLogAction;
use Illuminate\Http\JsonResponse;

class AdminServerLogController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/logs",
     *     summary="List all server log files",
     *     tags={"Admin Server Logs"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of log files",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="name", type="string", example="laravel.log"),
     *                 @OA\Property(property="size", type="integer", example=1024),
     *                 @OA\Property(property="updated_at", type="string", example="2026-02-07 11:00:00")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(ListServerLogsAction $action): JsonResponse
    {
        $logs = $action->execute();

        return self::successfulResponseWithData($logs);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/logs/{filename}",
     *     summary="Show log file content",
     *     tags={"Admin Server Logs"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="laravel.log")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Log file content",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="name", type="string", example="laravel.log"),
     *                 @OA\Property(property="content", type="string", example="[2026-02-07 11:00:00] local.INFO: ...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Invalid filename"),
     *     @OA\Response(response=404, description="Log file not found")
     * )
     */
    public function show(string $filename, ShowServerLogAction $action): JsonResponse
    {
        $logData = $action->execute($filename);

        return self::successfulResponseWithData($logData);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/logs/{filename}",
     *     summary="Clear log file content",
     *     tags={"Admin Server Logs"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="laravel.log")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Log cleared successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Log cleared successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Invalid filename"),
     *     @OA\Response(response=404, description="Log file not found")
     * )
     */
    public function clear(string $filename, ClearServerLogAction $action): JsonResponse
    {
        $action->execute($filename);

        return self::successfulResponse(message: 'Log cleared successfully');
    }
}
