<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Settings\GetSystemSettingsAction;
use App\Api\Admin\Actions\Settings\UpdateSystemSettingsAction;
use App\Api\Admin\Actions\Settings\UploadWatermarkAction;
use App\Api\Admin\Requests\Settings\UpdateSettingsRequest;
use App\Api\Admin\Requests\Settings\UploadWatermarkRequest;
use Illuminate\Http\JsonResponse;

class AdminSettingsController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/settings",
     *     summary="List all system settings grouped by category",
     *     tags={"Admin Settings"},
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
     *                 @OA\Property(property="settings", type="object"),
     *                 @OA\Property(property="groups", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function index(GetSystemSettingsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    /**
     * @OA\Post(
     *     path="/api/admin/settings",
     *     summary="Update system settings",
     *     tags={"Admin Settings"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="settings", type="object", example={"site_name": "Filkx Live", "maintenance_mode": false})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Settings updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings updated successfully")
     *         )
     *     )
     * )
     */
    public function update(UpdateSettingsRequest $request, UpdateSystemSettingsAction $action): JsonResponse
    {
        $action->execute(
            $request->input('settings'),
            $request->ip(),
            $request->userAgent()
        );

        return self::successfulResponse('Settings updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/settings/watermark",
     *     summary="Upload watermark",
     *     tags={"Admin Settings"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(property="watermark", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Watermark uploaded successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="url", type="string", example="http://example.com/storage/watermarks/1.png")
     *             )
     *         )
     *     )
     * )
     */
    public function uploadWatermark(UploadWatermarkRequest $request, UploadWatermarkAction $action)
    {
        $data = $action->execute($request->file('watermark'));

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
