<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\User\DeleteUserAvatarAction;
use App\Api\V1\Actions\User\GetUserAction;
use App\Api\V1\Actions\User\GetUserSessionsAction;
use App\Api\V1\Actions\User\InitiateAccountDeletionAction;
use App\Api\V1\Actions\User\RestoreDeletedAccountAction;
use App\Api\V1\Actions\User\RevokeAllUserSessionsAction;
use App\Api\V1\Actions\User\SetUserPasswordAction;
use App\Api\V1\Actions\User\UpdateUserLocaleAction;
use App\Api\V1\Actions\User\UpdateUserPasswordAction;
use App\Api\V1\Actions\User\UpdateUserProfileAction;
use App\Api\V1\Actions\User\UploadUserAvatarAction;
use App\Api\V1\Requests\User\SetUserPasswordRequest;
use App\Api\V1\Requests\User\UpdateNotificationPreferencesRequest;
use App\Api\V1\Requests\User\UpdateUserLocaleRequest;
use App\Api\V1\Requests\User\UpdateUserPasswordRequest;
use App\Api\V1\Requests\User\UpdateUserProfileRequest;
use App\Api\V1\Requests\User\UploadAvatarRequest;
use App\Api\V1\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/user/me",
     *     summary="Get authenticated user details",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     )
     * )
     */
    public function me(GetUserAction $action): JsonResponse
    {
        $user = $action->execute();

        return self::successfulResponseWithData(new UserResource($user));
    }

    /**
     * @OA\Post(
     *     path="/api/user/locale",
     *     summary="Update user locale",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserLocaleRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Locale updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     )
     * )
     */
    public function updateLocale(
        UpdateUserLocaleRequest $request,
        UpdateUserLocaleAction $action
    ): JsonResponse {
        $user = $action->execute(
            user: $request->user(),
            locale: $request->validated('locale')
        );

        return self::successfulResponseWithData(new UserResource($user));
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile",
     *     summary="Update user profile",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserProfileRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     )
     * )
     */
    public function updateProfile(
        UpdateUserProfileRequest $request,
        UpdateUserProfileAction $action
    ): JsonResponse {
        $user = $action->execute(
            user: $request->user(),
            data: $request->validated()
        );

        $message = 'Profile updated successfully';
        if ($user->getAttribute('email_change_pending')) {
            $message = 'Profile updated. Please check your current email to confirm the address change.';
        }

        return self::successfulResponseWithData(
            data: new UserResource($user),
            message: $message
        );
    }

    /**
     * @OA\Put(
     *     path="/api/user/password",
     *     summary="Update user password",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserPasswordRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Current password is incorrect",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Current password is incorrect")
     *         )
     *     )
     * )
     */
    public function updatePassword(
        UpdateUserPasswordRequest $request,
        UpdateUserPasswordAction $action
    ): JsonResponse {
        $result = $action->execute(
            user: $request->user(),
            currentPassword: $request->validated('currentPassword'),
            newPassword: $request->validated('newPassword')
        );

        if (! $result) {
            return self::errorResponse(
                message: 'Current password is incorrect',
                status: 400
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/user/avatar",
     *     summary="Upload user avatar",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(ref="#/components/schemas/UploadAvatarRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Avatar uploaded",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     )
     * )
     */
    public function uploadAvatar(
        UploadAvatarRequest $request,
        UploadUserAvatarAction $action
    ): JsonResponse {
        $user = $action->execute(
            user: $request->user(),
            avatar: $request->file('avatar')
        );

        return self::successfulResponseWithData(
            data: new UserResource($user)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/user/avatar",
     *     summary="Delete user avatar",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Avatar deleted",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     )
     * )
     */
    public function deleteAvatar(
        DeleteUserAvatarAction $action
    ): JsonResponse {
        $authUser = Auth::guard('api')
            ->user()
            ->load('roles.permissions', 'subscription');
        $user = $action->execute($authUser);

        return self::successfulResponseWithData(
            data: new UserResource($user)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/user/password/set",
     *     summary="Set password for OAuth users",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/SetUserPasswordRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password set successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function setPassword(
        SetUserPasswordRequest $request,
        SetUserPasswordAction $action
    ): JsonResponse {
        $user = $request->user();

        $hasPassword = ! empty($user->password) && ! Hash::check('', $user->password);
        $hasOAuth = $user->oauthAccounts()->exists();

        if ($hasPassword) {
            return self::errorResponse(
                message: 'Password already set. Please use the change password feature.',
                status: 400
            );
        }

        if (! $hasOAuth) {
            return self::errorResponse(
                message: 'Unauthorized operation.',
                status: 403
            );
        }

        $action->execute(
            user: $user,
            password: $request->validated('password')
        );

        return response()->json([
            'success' => true,
            'message' => 'Password set successfully',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/settings/preferences",
     *     summary="Get notification preferences",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Notification preferences"
     *     )
     * )
     */
    public function getPreferences(): JsonResponse
    {
        $user = auth()->user();
        $preferences = $user->notification_preferences ?? [
            'newsletter' => false,
            'product_updates' => true,
            'marketing_emails' => false,
        ];

        return self::successfulResponseWithData(['preferences' => $preferences]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/settings/preferences",
     *     summary="Update notification preferences",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateNotificationPreferencesRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Preferences updated"
     *     )
     * )
     */
    public function updatePreferences(
        UpdateNotificationPreferencesRequest $request
    ): JsonResponse {
        $user = auth()->user();
        $user->notification_preferences = $request->validated();
        $user->save();

        return self::successfulResponseWithData([
            'message' => 'Notification preferences updated successfully',
            'preferences' => $user->notification_preferences,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/sessions",
     *     summary="Get active sessions",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of active sessions"
     *     )
     * )
     */
    public function sessions(
        GetUserSessionsAction $action
    ): JsonResponse {
        $sessions = $action->execute(auth()->user());

        return self::successfulResponseWithData(['sessions' => $sessions]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/sessions/logout-all",
     *     summary="Logout from all sessions except current",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Sessions revoked"
     *     )
     * )
     */
    public function logoutAll(
        RevokeAllUserSessionsAction $action
    ): JsonResponse {
        $count = $action->execute(auth()->user());

        return self::successfulResponseWithData([
            'message' => "Logged out from {$count} session(s)",
            'revoked_count' => $count,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/delete",
     *     summary="Initiate account deletion",
     *     tags={"User"},
     *     security={{"passport": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Account deletion initiated"
     *     )
     * )
     */
    public function initiateDelete(
        InitiateAccountDeletionAction $action
    ): JsonResponse {
        $action->execute(auth()->user());

        return self::successfulResponseWithData([
            'message' => 'Account deletion initiated. You will receive an email with restoration instructions.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/restore",
     *     summary="Restore deleted account",
     *     tags={"User"},
     *
     *     @OA\Parameter(
     *         name="userId",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to frontend"
     *     )
     * )
     */
    public function restore(
        Request $request,
        RestoreDeletedAccountAction $action
    ): RedirectResponse|JsonResponse {
        // Validate signed URL
        if (! $request->hasValidSignature()) {
            // If signature is invalid, we might still want to redirect to a frontend error page
            // But returning JSON/Error for now as per original, or redirecting to generic error
            return response()->json([
                'message' => 'Invalid or expired restoration link',
            ], 400);
        }

        try {
            $user = User::withTrashed()->findOrFail($request->userId);
            $action->execute($user);

            // Redirect to frontend dashboard or login with a success message param
            $frontendUrl = config('app.frontend_url', 'https://live.filkx.com');

            return redirect()->to($frontendUrl.'/login?status=restored');
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/email/confirm-change",
     *     summary="Confirm email change",
     *     tags={"User"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="new_email",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="email")
     *     ),
     *
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to frontend"
     *     )
     * )
     */
    public function confirmEmailChange(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired confirmation link.');
        }

        $user = User::findOrFail($request->id);
        $newEmail = $request->new_email;

        // Double check if email is still available (unlikely but possible race condition)
        if (User::where('email', $newEmail)->exists()) {
            // Ideally redirect with error
            $frontendUrl = config('app.frontend_url', 'https://live.filkx.com');

            return redirect()->to($frontendUrl.'/settings?error=email_taken');
        }

        $user->email = $newEmail;
        $user->email_verified_at = null;
        $user->save();

        // Send verification to NEW email
        $user->sendEmailVerificationNotification();

        $frontendUrl = config('app.frontend_url', 'https://live.filkx.com');

        return redirect()->to($frontendUrl.'/login?status=email-changed');
    }
}
