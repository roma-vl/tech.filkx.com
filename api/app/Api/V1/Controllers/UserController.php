<?php

namespace App\Api\V1\Controllers;

use App\Api\Admin\Actions\Accounting\GenerateInvoicePdfAction;
use App\Api\V1\Actions\User\Compare\GetUserComparesAction;
use App\Api\V1\Actions\User\Compare\SyncUserComparesAction;
use App\Api\V1\Actions\User\Compare\ToggleUserCompareAction;
use App\Api\V1\Actions\User\ConfirmEmailChangeAction;
use App\Api\V1\Actions\User\DeleteUserAvatarAction;
use App\Api\V1\Actions\User\Favorites\GetUserFavoritesAction;
use App\Api\V1\Actions\User\Favorites\SyncUserFavoritesAction;
use App\Api\V1\Actions\User\Favorites\ToggleUserFavoriteAction;
use App\Api\V1\Actions\User\GetUserAction;
use App\Api\V1\Actions\User\GetUserNotificationPreferencesAction;
use App\Api\V1\Actions\User\GetUserSessionsAction;
use App\Api\V1\Actions\User\InitiateAccountDeletionAction;
use App\Api\V1\Actions\User\Order\CancelUserOrderAction;
use App\Api\V1\Actions\User\Order\GetUserOrdersAction;
use App\Api\V1\Actions\User\Order\UserOwnsOrderAction;
use App\Api\V1\Actions\User\RestoreDeletedAccountAction;
use App\Api\V1\Actions\User\RevokeAllUserSessionsAction;
use App\Api\V1\Actions\User\SetUserPasswordAction;
use App\Api\V1\Actions\User\UpdateUserLocaleAction;
use App\Api\V1\Actions\User\UpdateUserNotificationPreferencesAction;
use App\Api\V1\Actions\User\UpdateUserPasswordAction;
use App\Api\V1\Actions\User\UpdateUserProfileAction;
use App\Api\V1\Actions\User\UploadUserAvatarAction;
use App\Api\V1\Actions\User\ViewedProducts\ClearViewedProductsAction;
use App\Api\V1\Actions\User\ViewedProducts\GetUserViewedProductsAction;
use App\Api\V1\Actions\User\ViewedProducts\SyncViewedProductsAction;
use App\Api\V1\Actions\User\ViewedProducts\TrackViewedProductAction;
use App\Api\V1\Exceptions\OrderAccessDeniedException;
use App\Api\V1\Exceptions\OrderAlreadyCancelledException;
use App\Api\V1\Exceptions\OrderNotCancellableException;
use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Api\V1\Requests\User\SetUserPasswordRequest;
use App\Api\V1\Requests\User\UpdateNotificationPreferencesRequest;
use App\Api\V1\Requests\User\UpdateUserLocaleRequest;
use App\Api\V1\Requests\User\UpdateUserPasswordRequest;
use App\Api\V1\Requests\User\UpdateUserProfileRequest;
use App\Api\V1\Requests\User\UploadAvatarRequest;
use App\Api\V1\Resources\User\UserResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class UserController extends BaseApiController
{
    #[OA\Get(
        path: '/api/user/me',
        summary: 'Get authenticated user details',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User details',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource'),
            ),
        ],
    )]
    public function me(GetUserAction $action): JsonResponse
    {
        $user = $action->execute();

        return self::successfulResponseWithData(new UserResource($user));
    }

    #[OA\Post(
        path: '/api/user/locale',
        summary: 'Update user locale',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserLocaleRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Locale updated',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource'),
            ),
        ],
    )]
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

    #[OA\Put(
        path: '/api/user/profile',
        summary: 'Update user profile',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserProfileRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated (or update pending confirmation, when the email changed)',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource'),
            ),
        ],
    )]
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

    #[OA\Put(
        path: '/api/user/password',
        summary: 'Update user password',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserPasswordRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Password updated successfully'),
                    ],
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Current password is incorrect',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Current password is incorrect'),
                    ],
                ),
            ),
        ],
    )]
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

    #[OA\Post(
        path: '/api/user/avatar',
        summary: 'Upload user avatar',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UploadAvatarRequest'),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar uploaded',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource'),
            ),
        ],
    )]
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

    #[OA\Delete(
        path: '/api/user/avatar',
        summary: 'Delete user avatar',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource'),
            ),
        ],
    )]
    public function deleteAvatar(
        DeleteUserAvatarAction $action
    ): JsonResponse {
        $authUser = Auth::guard('api')
            ->user()
            ->load('roles.permissions');
        $user = $action->execute($authUser);

        return self::successfulResponseWithData(
            data: new UserResource($user)
        );
    }

    #[OA\Post(
        path: '/api/user/password/set',
        summary: 'Set password for OAuth users',
        description: 'Lets a user who registered via OAuth (and therefore has no password) set one for the first time.',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SetUserPasswordRequest'),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password set successfully'),
            new OA\Response(response: 400, description: 'A password is already set for this account'),
            new OA\Response(response: 403, description: 'Account has no linked OAuth provider'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/user/settings/preferences',
        summary: 'Get notification preferences',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification preferences',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'preferences',
                            properties: [
                                new OA\Property(property: 'newsletter', type: 'boolean'),
                                new OA\Property(property: 'product_updates', type: 'boolean'),
                                new OA\Property(property: 'marketing_emails', type: 'boolean'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function getPreferences(
        Request $request,
        GetUserNotificationPreferencesAction $action
    ): JsonResponse {
        return self::successfulResponseWithData([
            'preferences' => $action->execute($request->user()),
        ]);
    }

    #[OA\Put(
        path: '/api/user/settings/preferences',
        summary: 'Update notification preferences',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateNotificationPreferencesRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Preferences updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Notification preferences updated successfully'),
                        new OA\Property(property: 'preferences', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function updatePreferences(
        UpdateNotificationPreferencesRequest $request,
        UpdateUserNotificationPreferencesAction $action
    ): JsonResponse {
        $preferences = $action->execute($request->user(), $request->validated());

        return self::successfulResponseWithData([
            'message' => 'Notification preferences updated successfully',
            'preferences' => $preferences,
        ]);
    }

    #[OA\Get(
        path: '/api/user/sessions',
        summary: 'Get active sessions',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'List of active sessions'),
        ],
    )]
    public function sessions(
        GetUserSessionsAction $action
    ): JsonResponse {
        $sessions = $action->execute(auth()->user());

        return self::successfulResponseWithData(['sessions' => $sessions]);
    }

    #[OA\Post(
        path: '/api/user/sessions/logout-all',
        summary: 'Logout from all sessions except current',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'Sessions revoked'),
        ],
    )]
    public function logoutAll(
        RevokeAllUserSessionsAction $action
    ): JsonResponse {
        $count = $action->execute(auth()->user());

        return self::successfulResponseWithData([
            'message' => "Logged out from {$count} session(s)",
            'revoked_count' => $count,
        ]);
    }

    #[OA\Post(
        path: '/api/user/delete',
        summary: 'Initiate account deletion',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'Account deletion initiated'),
        ],
    )]
    public function initiateDelete(
        InitiateAccountDeletionAction $action
    ): JsonResponse {
        $action->execute(auth()->user());

        return self::successfulResponseWithData([
            'message' => 'Account deletion initiated. You will receive an email with restoration instructions.',
        ]);
    }

    #[OA\Get(
        path: '/api/user/restore',
        summary: 'Restore a soft-deleted account via signed link',
        description: 'The signed link is issued by AccountDeletionScheduledNotification, sent when initiateDelete runs.',
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'signature', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to frontend login with restored status'),
            new OA\Response(response: 400, description: 'Invalid/expired signature, or account not found'),
        ],
    )]
    public function restore(
        Request $request,
        RestoreDeletedAccountAction $action
    ): RedirectResponse|JsonResponse {
        if (! $request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid or expired restoration link',
            ], 400);
        }

        try {
            $action->execute((int) $request->userId);

            $frontendUrl = config('app.frontend_url', 'https://tech.filkx.com');

            return redirect()->to($frontendUrl.'/login?status=restored');
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[OA\Get(
        path: '/api/user/email/confirm-change',
        summary: 'Confirm a pending email change via signed link',
        description: 'Not currently wired to any route - the signed link is never issued by any notification in this codebase. Kept documented for when the email-change flow is implemented.',
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'new_email', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'email')),
            new OA\Parameter(name: 'signature', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to frontend (success or email-taken error)'),
            new OA\Response(response: 403, description: 'Invalid or expired confirmation link'),
        ],
    )]
    public function confirmEmailChange(
        Request $request,
        ConfirmEmailChangeAction $action
    ): RedirectResponse {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired confirmation link.');
        }

        $frontendUrl = config('app.frontend_url', 'https://tech.filkx.com');
        $confirmed = $action->execute((int) $request->id, $request->new_email);

        return redirect()->to($confirmed
            ? $frontendUrl.'/login?status=email-changed'
            : $frontendUrl.'/settings?error=email_taken');
    }

    #[OA\Get(
        path: '/api/user/favorites',
        summary: 'Get favorited products',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'List of favorited products'),
        ],
    )]
    public function getFavorites(Request $request, GetUserFavoritesAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->user()));
    }

    #[OA\Post(
        path: '/api/user/favorites/toggle',
        summary: 'Add or remove a product from favorites',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id'],
                properties: [new OA\Property(property: 'product_id', type: 'integer')],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated list of favorited products'),
            new OA\Response(response: 400, description: 'Product ID is required'),
            new OA\Response(response: 404, description: 'Product not found'),
        ],
    )]
    public function toggleFavorite(Request $request, ToggleUserFavoriteAction $action): JsonResponse
    {
        $productId = $request->input('product_id');
        if (! $productId) {
            return self::errorResponse('Product ID is required.', 400);
        }

        $product = Product::find($productId);
        if (! $product) {
            return self::errorResponse('Product not found.', 404);
        }

        return self::successfulResponseWithData($action->execute($request->user(), $product));
    }

    #[OA\Post(
        path: '/api/user/favorites/sync',
        summary: 'Merge a client-side favorites list into the user\'s account',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'product_ids', type: 'array', items: new OA\Items(type: 'integer')),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Full list of favorited products after sync'),
        ],
    )]
    public function syncFavorites(Request $request, SyncUserFavoritesAction $action): JsonResponse
    {
        return self::successfulResponseWithData(
            $action->execute($request->user(), $request->input('product_ids', []))
        );
    }

    #[OA\Get(
        path: '/api/user/compares',
        summary: 'Get products in the comparison list',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'List of products in comparison'),
        ],
    )]
    public function getCompares(Request $request, GetUserComparesAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->user()));
    }

    #[OA\Post(
        path: '/api/user/compares/toggle',
        summary: 'Add or remove a product from the comparison list',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id'],
                properties: [new OA\Property(property: 'product_id', type: 'integer')],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated comparison list'),
            new OA\Response(response: 400, description: 'Product ID is required'),
        ],
    )]
    public function toggleCompare(Request $request, ToggleUserCompareAction $action): JsonResponse
    {
        $productId = $request->input('product_id');
        if (! $productId) {
            return self::errorResponse('Product ID is required.', 400);
        }

        return self::successfulResponseWithData($action->execute($request->user(), (int) $productId));
    }

    #[OA\Post(
        path: '/api/user/compares/sync',
        summary: 'Merge a client-side comparison list into the user\'s account',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'product_ids', type: 'array', items: new OA\Items(type: 'integer')),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Full comparison list after sync'),
        ],
    )]
    public function syncCompares(Request $request, SyncUserComparesAction $action): JsonResponse
    {
        return self::successfulResponseWithData(
            $action->execute($request->user(), $request->input('product_ids', []))
        );
    }

    #[OA\Get(
        path: '/api/user/viewed-products',
        summary: 'Get recently viewed products',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'Recently viewed products, most recent first'),
        ],
    )]
    public function getViewedProducts(Request $request, GetUserViewedProductsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->user()));
    }

    #[OA\Post(
        path: '/api/user/viewed-products/track',
        summary: 'Record a product view',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id'],
                properties: [new OA\Property(property: 'product_id', type: 'integer')],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product tracked successfully'),
            new OA\Response(response: 400, description: 'Product ID is required'),
        ],
    )]
    public function trackViewedProduct(Request $request, TrackViewedProductAction $action): JsonResponse
    {
        $productId = $request->input('product_id');
        if (! $productId) {
            return self::errorResponse('Product ID is required.', 400);
        }

        $action->execute($request->user(), (int) $productId);

        return self::successfulResponse('Product tracked successfully.');
    }

    #[OA\Post(
        path: '/api/user/viewed-products/sync',
        summary: 'Merge a client-side viewing history into the user\'s account',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'view_count', type: 'integer'),
                                new OA\Property(property: 'last_viewed_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object',
                        ),
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Recently viewed products after merge'),
        ],
    )]
    public function syncViewedProducts(Request $request, SyncViewedProductsAction $action): JsonResponse
    {
        return self::successfulResponseWithData(
            $action->execute($request->user(), $request->input('items', []))
        );
    }

    #[OA\Delete(
        path: '/api/user/viewed-products/clear',
        summary: 'Clear viewed products history',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'History cleared'),
        ],
    )]
    public function clearViewedProducts(Request $request, ClearViewedProductsAction $action): JsonResponse
    {
        $action->execute($request->user());

        return self::successfulResponse('Viewed products history cleared.');
    }

    #[OA\Get(
        path: '/api/user/orders/{id}/invoice',
        summary: 'Download an order invoice as PDF',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invoice PDF stream'),
            new OA\Response(response: 403, description: 'Order does not belong to this user'),
            new OA\Response(response: 404, description: 'Order not found'),
        ],
    )]
    public function downloadInvoice(
        Request $request,
        int $id,
        GenerateInvoicePdfAction $action,
        UserOwnsOrderAction $userOwnsOrderAction
    ): mixed {
        $user = $request->user();
        $order = Order::with('items')->find($id);

        if (! $order) {
            return self::errorResponse('Замовлення не знайдено', 404);
        }

        if (! $userOwnsOrderAction->execute($user, $order)) {
            return self::errorResponse('У вас немає доступу до цього замовлення', 403);
        }

        return $action->execute($order)->download("invoice-{$order->order_number}.pdf");
    }

    #[OA\Get(
        path: '/api/user/orders',
        summary: 'Get the authenticated user\'s order history',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'Orders, most recent first, with Ukrainian-localized status/tracking'),
        ],
    )]
    public function getOrders(Request $request, GetUserOrdersAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->user()));
    }

    #[OA\Post(
        path: '/api/user/orders/{id}/cancel',
        summary: 'Cancel an order',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Order cancelled successfully'),
            new OA\Response(response: 400, description: 'Order is already cancelled'),
            new OA\Response(response: 403, description: 'Order does not belong to this user'),
            new OA\Response(response: 404, description: 'Order not found'),
            new OA\Response(response: 422, description: 'Order already shipped/delivered/completed/refunded and can no longer be self-cancelled'),
        ],
    )]
    public function cancelOrder(Request $request, int $id, CancelUserOrderAction $action): JsonResponse
    {
        try {
            $action->execute($request->user(), $id);
        } catch (OrderNotFoundException $e) {
            return self::errorResponse($e->getMessage(), 404);
        } catch (OrderAccessDeniedException $e) {
            return self::errorResponse($e->getMessage(), 403);
        } catch (OrderNotCancellableException $e) {
            return self::errorResponse($e->getMessage(), 422);
        } catch (OrderAlreadyCancelledException $e) {
            return self::errorResponse($e->getMessage(), 400);
        }

        return self::successfulResponse('Замовлення успішно скасовано.');
    }
}
