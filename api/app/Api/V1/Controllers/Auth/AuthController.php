<?php

namespace App\Api\V1\Controllers\Auth;

use App\Api\V1\Controllers\BaseApiController;
use App\Api\V1\Dto\Auth\ForgotPasswordDto;
use App\Api\V1\Dto\Auth\LoginDto;
use App\Api\V1\Dto\Auth\RegisterDto;
use App\Api\V1\Dto\Auth\ResetPasswordDto;
use App\Api\V1\Requests\Auth\ForgotPasswordRequest;
use App\Api\V1\Requests\Auth\LoginRequest;
use App\Api\V1\Requests\Auth\RegisterRequest;
use App\Api\V1\Requests\Auth\ResendVerificationRequest;
use App\Api\V1\Requests\Auth\ResetPasswordRequest;
use App\Api\V1\Resources\User\UserResource;
use App\Api\V1\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get authenticated user",
     *     tags={"Auth"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Current user",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        if ($user) {
            $user->load('roles');
        }

        return self::successfulResponseWithData(new UserResource($user));
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register new user",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", ref="#/components/schemas/AuthToken"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterDto(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString()
        );

        $result = $this->service->register($dto);

        return self::successfulResponseWithData($result);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", ref="#/components/schemas/AuthToken"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = new LoginDto(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString()
        );

        $result = $this->service->login($dto);

        return self::successfulResponseWithData($result);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(response=200, description="Logged out")
     * )
     */
    public function logout(): JsonResponse
    {
        $this->service->logout(auth()->user());

        return self::successfulResponse();
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh auth token",
     *     tags={"Auth"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", ref="#/components/schemas/AuthToken")
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        $result = $this->service->refreshToken(auth()->user());

        return self::successfulResponseWithData($result);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/verify-email",
     *     summary="Verify email",
     *     tags={"Auth"},
     *
     *     @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="hash", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="expires", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="signature", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Email verified")
     * )
     */
    public function verifyEmailByParams(Request $request): JsonResponse
    {
        $id = $request->query('id');
        $hash = $request->query('hash');
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        $message = $this->service->verifyEmail(
            (string) $id,
            (string) $hash,
            (string) $expires,
            (string) $signature
        );

        return self::successfulResponseWithData($message);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/resend",
     *     summary="Resend verification email",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ResendVerificationRequest")),
     *
     *     @OA\Response(response=200, description="Email sent")
     * )
     */
    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        $this->service->resendVerificationEmail(
            $request->string('email')->toString()
        );

        return self::successfulResponseWithData('Verification email sent');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/password/forgot",
     *     summary="Request password reset",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")),
     *
     *     @OA\Response(response=200, description="Reset link sent")
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $dto = new ForgotPasswordDto(
            email: $request->string('email')->toString()
        );

        $this->service->sendResetLink($dto);

        return self::successfulResponseWithData('Password reset link sent');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/password/reset",
     *     summary="Reset password",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")),
     *
     *     @OA\Response(response=200, description="Password reset success")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $dto = new ResetPasswordDto(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            token: $request->string('token')->toString()
        );

        $this->service->resetPassword($dto);

        return self::successfulResponseWithData('Password reset successfully');
    }
}
