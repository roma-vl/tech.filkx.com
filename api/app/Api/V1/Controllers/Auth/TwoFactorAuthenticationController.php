<?php

namespace App\Api\V1\Controllers\Auth;

use App\Api\V1\Actions\User\TwoFactor\ConfirmTwoFactorAuthenticationAction;
use App\Api\V1\Actions\User\TwoFactor\DisableTwoFactorAuthenticationAction;
use App\Api\V1\Actions\User\TwoFactor\EnableTwoFactorAuthenticationAction;
use App\Api\V1\Actions\User\TwoFactor\RegenerateRecoveryCodesAction;
use App\Api\V1\Controllers\BaseApiController;
use App\Api\V1\Requests\User\ConfirmTwoFactorAuthenticationRequest;
use App\Api\V1\Requests\User\DisableTwoFactorAuthenticationRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class TwoFactorAuthenticationController extends BaseApiController
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/2fa/enable",
     *     summary="Start two-factor authentication enrollment",
     *     tags={"User", "Security"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Secret and QR code URL for the authenticator app",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="secret", type="string"),
     *             @OA\Property(property="qrCodeUrl", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Two-factor authentication is already enabled")
     * )
     */
    public function enable(EnableTwoFactorAuthenticationAction $action): JsonResponse
    {
        $result = $action->execute(auth()->user());

        return self::successfulResponseWithData($result);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/2fa/confirm",
     *     summary="Confirm two-factor enrollment with a code from the authenticator app",
     *     tags={"User", "Security"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ConfirmTwoFactorAuthenticationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Two-factor authentication enabled; one-time recovery codes",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="recoveryCodes", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Invalid code or no pending enrollment")
     * )
     */
    public function confirm(ConfirmTwoFactorAuthenticationRequest $request, ConfirmTwoFactorAuthenticationAction $action): JsonResponse
    {
        $recoveryCodes = $action->execute(auth()->user(), $request->string('code')->toString());

        return self::successfulResponseWithData(['recovery_codes' => $recoveryCodes]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/2fa/disable",
     *     summary="Disable two-factor authentication",
     *     tags={"User", "Security"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/DisableTwoFactorAuthenticationRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Two-factor authentication disabled"),
     *     @OA\Response(response=422, description="Incorrect password")
     * )
     */
    public function disable(DisableTwoFactorAuthenticationRequest $request, DisableTwoFactorAuthenticationAction $action): JsonResponse
    {
        $action->execute(auth()->user(), $request->string('password')->toString());

        return self::successfulResponse();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/2fa/recovery-codes/regenerate",
     *     summary="Regenerate one-time recovery codes",
     *     tags={"User", "Security"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ConfirmTwoFactorAuthenticationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="New one-time recovery codes",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="recoveryCodes", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Invalid code or 2FA not enabled")
     * )
     */
    public function regenerateRecoveryCodes(ConfirmTwoFactorAuthenticationRequest $request, RegenerateRecoveryCodesAction $action): JsonResponse
    {
        $recoveryCodes = $action->execute(auth()->user(), $request->string('code')->toString());

        return self::successfulResponseWithData(['recovery_codes' => $recoveryCodes]);
    }
}
