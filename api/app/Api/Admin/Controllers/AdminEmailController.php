<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Email\ListEmailCampaignsAction;
use App\Api\Admin\Actions\Email\PreviewEmailCampaignAction;
use App\Api\Admin\Actions\Email\SendEmailCampaignAction;
use App\Api\Admin\Requests\Email\PreviewCampaignRequest;
use App\Api\Admin\Requests\Email\SendCampaignRequest;
use Illuminate\Http\JsonResponse;

class AdminEmailController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/emails/campaigns",
     *     summary="List available email campaign templates",
     *     tags={"Admin Emails"},
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
     *                 @OA\Property(property="campaigns", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", example="platform_update"),
     *                     @OA\Property(property="name", type="string", example="Monthly Platform Update"),
     *                     @OA\Property(property="class", type="string", example="App\\Mail\\Campaigns\\PlatformUpdateMail"),
     *                     @OA\Property(property="description", type="string", example="Notify users about new features."),
     *                     @OA\Property(property="suggested_audience", type="string", example="all")
     *                 ))
     *             )
     *         )
     *     )
     * )
     */
    public function index(ListEmailCampaignsAction $action): JsonResponse
    {
        return self::successfulResponseWithData([
            'campaigns' => $action->execute(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/emails/send",
     *     summary="Trigger a campaign broadcast",
     *     tags={"Admin Emails"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="campaignClass", type="string", example="App\\Mail\\Campaigns\\PlatformUpdateMail"),
     *             @OA\Property(property="audience", type="string", enum={"all", "trial_expired", "new_users", "active_subscribers"}, example="all")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Campaign queued successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="count", type="integer")
     *         )
     *     )
     * )
     */
    public function send(SendCampaignRequest $request, SendEmailCampaignAction $action): JsonResponse
    {
        $count = $action->execute(
            $request->input('campaignClass'),
            $request->input('audience')
        );

        return self::successfulResponse(
            "Campaign queued for {$count} users successfully.",
            ['count' => $count]
        );
    }

    /**
     * @OA\Post(
     *     path="/api/admin/emails/preview",
     *     summary="Preview a campaign template",
     *     tags={"Admin Emails"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="campaignClass", type="string", example="App\\Mail\\Campaigns\\PlatformUpdateMail")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="HTML preview of the email",
     *
     *         @OA\MediaType(mediaType="text/html")
     *     )
     * )
     */
    public function preview(PreviewCampaignRequest $request, PreviewEmailCampaignAction $action)
    {
        try {
            return $action->execute($request->input('campaignClass'));
        } catch (\InvalidArgumentException $e) {
            return self::errorResponse($e->getMessage(), 422);
        }
    }
}
