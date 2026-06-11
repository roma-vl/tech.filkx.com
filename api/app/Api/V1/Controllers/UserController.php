<?php

namespace App\Api\V1\Controllers;

use App\Api\Admin\Actions\Order\UpdateAdminOrderStatusAction;
use App\Api\Admin\Dto\UpdateOrderStatusDto;
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
use App\Api\V1\Repositories\ProductRepository;
use App\Api\V1\Requests\User\SetUserPasswordRequest;
use App\Api\V1\Requests\User\UpdateNotificationPreferencesRequest;
use App\Api\V1\Requests\User\UpdateUserLocaleRequest;
use App\Api\V1\Requests\User\UpdateUserPasswordRequest;
use App\Api\V1\Requests\User\UpdateUserProfileRequest;
use App\Api\V1\Requests\User\UploadAvatarRequest;
use App\Api\V1\Resources\User\UserResource;
use App\Models\Order;
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
            ->load('roles.permissions');
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

    public function getFavorites(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productIds = $request->user()->favorites()->pluck('product_id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $productIds)->get();

        return self::successfulResponseWithData($products);
    }

    public function toggleFavorite(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productId = $request->input('product_id');
        if (! $productId) {
            return self::errorResponse('Product ID is required.', 400);
        }

        $user = $request->user();
        $user->favorites()->toggle($productId);

        $productIds = $user->favorites()->pluck('product_id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $productIds)->get();

        return self::successfulResponseWithData($products);
    }

    public function syncFavorites(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productIds = $request->input('product_ids', []);
        $user = $request->user();
        if (! empty($productIds)) {
            $user->favorites()->syncWithoutDetaching($productIds);
        }

        $allIds = $user->favorites()->pluck('product_id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $allIds)->get();

        return self::successfulResponseWithData($products);
    }

    public function getCompares(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productIds = $request->user()->compares()->pluck('product_id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $productIds)->get();

        return self::successfulResponseWithData($products);
    }

    public function toggleCompare(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productId = $request->input('product_id');
        if (! $productId) {
            return self::errorResponse('Product ID is required.', 400);
        }

        $user = $request->user();
        $user->compares()->toggle($productId);

        $productIds = $user->compares()->pluck('product_id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $productIds)->get();

        return self::successfulResponseWithData($products);
    }

    public function syncCompares(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productIds = $request->input('product_ids', []);
        $user = $request->user();
        if (! empty($productIds)) {
            $user->compares()->syncWithoutDetaching($productIds);
        }

        $allIds = $user->compares()->pluck('product_id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $allIds)->get();

        return self::successfulResponseWithData($products);
    }

    public function getViewedProducts(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $viewedItems = $request->user()->viewedProducts()
            ->withPivot('view_count')
            ->orderByPivot('updated_at', 'desc')
            ->get();

        $productIds = $viewedItems->pluck('id')->toArray();
        $products = $productRepository->queryActive()->whereIn('id', $productIds)->get();

        $productsMapped = $products->map(function ($product) use ($viewedItems) {
            $viewed = $viewedItems->firstWhere('id', $product->id);
            $product->view_count = $viewed ? $viewed->pivot->view_count : 1;
            $product->last_viewed_at = $viewed ? $viewed->pivot->updated_at->toISOString() : now()->toISOString();

            return $product;
        });

        $productsMappedSorted = $productsMapped->sortByDesc('last_viewed_at')->values();

        return self::successfulResponseWithData($productsMappedSorted);
    }

    public function trackViewedProduct(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productId = $request->input('product_id');
        if (! $productId) {
            return self::errorResponse('Product ID is required.', 400);
        }

        $user = $request->user();
        $existing = $user->viewedProducts()->where('product_id', $productId)->first();

        if ($existing) {
            $user->viewedProducts()->updateExistingPivot($productId, [
                'view_count' => $existing->pivot->view_count + 1,
                'updated_at' => now(),
            ]);
        } else {
            $user->viewedProducts()->attach($productId, [
                'view_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return self::successfulResponse('Product tracked successfully.');
    }

    public function syncViewedProducts(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $items = $request->input('items', []);
        $user = $request->user();

        foreach ($items as $item) {
            $pId = $item['id'] ?? null;
            if (! $pId) {
                continue;
            }

            $existing = $user->viewedProducts()->where('product_id', $pId)->first();
            if ($existing) {
                $newCount = max($existing->pivot->view_count, $item['view_count'] ?? 1);
                $newTime = ! empty($item['last_viewed_at']) && strtotime($item['last_viewed_at']) > $existing->pivot->updated_at->timestamp
                    ? date('Y-m-d H:i:s', strtotime($item['last_viewed_at']))
                    : $existing->pivot->updated_at;

                $user->viewedProducts()->updateExistingPivot($pId, [
                    'view_count' => $newCount,
                    'updated_at' => $newTime,
                ]);
            } else {
                $user->viewedProducts()->attach($pId, [
                    'view_count' => $item['view_count'] ?? 1,
                    'created_at' => ! empty($item['last_viewed_at']) ? date('Y-m-d H:i:s', strtotime($item['last_viewed_at'])) : now(),
                    'updated_at' => ! empty($item['last_viewed_at']) ? date('Y-m-d H:i:s', strtotime($item['last_viewed_at'])) : now(),
                ]);
            }
        }

        return $this->getViewedProducts($request, $productRepository);
    }

    public function clearViewedProducts(Request $request): JsonResponse
    {
        $request->user()->viewedProducts()->detach();

        return self::successfulResponse('Viewed products history cleared.');
    }

    public function getOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::with(['items.variant.product'])
            ->where('user_id', $user->id)
            ->orWhere('customer_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedOrders = $orders->map(function ($order) {
            $mappedStatus = $this->mapOrderStatus($order->status, $order->updated_at);

            $itemsMapped = $order->items->map(function ($item) {
                $variant = $item->variant;
                $images = $variant ? ($variant->dimensions['images'] ?? []) : [];
                $imageUrl = ! empty($images) ? ($images[0]['url'] ?? null) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop';

                return [
                    'id' => $item->id,
                    'slug' => $variant && $variant->product ? $variant->product->slug : '',
                    'name' => $item->product_name,
                    'price' => (float) $item->price,
                    'image' => $imageUrl,
                    'qty' => $item->quantity,
                ];
            })->toArray();

            return [
                'id' => $order->order_number ?: (string) $order->id,
                'dbId' => $order->id,
                'date' => $this->formatUkrainianDate($order->created_at),
                'total' => (float) $order->total_price,
                'shipTo' => $order->customer_name ?: '',
                'status' => $mappedStatus['status'],
                'statusIcon' => $mappedStatus['statusIcon'],
                'statusClass' => $mappedStatus['statusClass'],
                'statusCode' => $mappedStatus['statusCode'],
                'trackingSteps' => $this->generateTrackingSteps($mappedStatus['statusCode'], $order->created_at, $order->updated_at),
                'shippingAddress' => [
                    'recipient' => $order->customer_name ?: '',
                    'street' => $order->shipping_address ?: '',
                    'city' => $order->shipping_city ?: 'Київ',
                    'state' => 'Київська обл.',
                    'zip' => '01001',
                    'country' => $order->shipping_country ?: 'Україна',
                ],
                'paymentMethod' => [
                    'type' => $order->payment_method ?: 'Картка',
                    'number' => '•••• 4242',
                ],
                'items' => $itemsMapped,
            ];
        });

        return self::successfulResponseWithData($formattedOrders);
    }

    protected function formatUkrainianDate($carbonDate): string
    {
        $months = [
            1 => 'Січ', 2 => 'Лют', 3 => 'Бер', 4 => 'Кві', 5 => 'Тра', 6 => 'Чер',
            7 => 'Лип', 8 => 'Сер', 9 => 'Вер', 10 => 'Жов', 11 => 'Лис', 12 => 'Гру',
        ];

        $day = $carbonDate->format('d');
        $monthNum = (int) $carbonDate->format('m');
        $year = $carbonDate->format('Y');

        return "{$day} {$months[$monthNum]}, {$year}";
    }

    protected function mapOrderStatus($dbStatus, $updatedAt): array
    {
        $formattedUpdateDate = $this->formatUkrainianDate($updatedAt);

        switch ($dbStatus) {
            case 'completed':
                return [
                    'statusCode' => 'completed',
                    'status' => "Виконано {$formattedUpdateDate}",
                    'statusIcon' => 'task_alt',
                    'statusClass' => 'text-zinc-500 dark:text-zinc-455 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-700',
                ];
            case 'delivered':
                return [
                    'statusCode' => 'delivered',
                    'status' => "Доставлено {$formattedUpdateDate}",
                    'statusIcon' => 'check_circle',
                    'statusClass' => 'text-teal-500 bg-teal-550/10 border border-teal-550/20',
                ];
            case 'cancelled':
                return [
                    'statusCode' => 'cancelled',
                    'status' => "Скасовано {$formattedUpdateDate}",
                    'statusIcon' => 'cancel',
                    'statusClass' => 'text-rose-500 bg-rose-500/10 border border-rose-500/20',
                ];
            case 'refunded':
                return [
                    'statusCode' => 'refunded',
                    'status' => "Повернуто кошти {$formattedUpdateDate}",
                    'statusIcon' => 'undo',
                    'statusClass' => 'text-gray-500 bg-gray-500/10 border border-gray-500/20',
                ];
            case 'shipped':
                return [
                    'statusCode' => 'shipped',
                    'status' => 'Відправлено - в дорозі',
                    'statusIcon' => 'local_shipping',
                    'statusClass' => 'text-[#00a046] bg-emerald-500/10 border border-emerald-500/20',
                ];
            case 'pending_payment':
                return [
                    'statusCode' => 'pending_payment',
                    'status' => 'Очікує оплати',
                    'statusIcon' => 'hourglass_empty',
                    'statusClass' => 'text-amber-500 bg-amber-500/10 border border-amber-500/20',
                ];
            case 'paid':
                return [
                    'statusCode' => 'paid',
                    'status' => 'Оплачено',
                    'statusIcon' => 'payments',
                    'statusClass' => 'text-emerald-500 bg-emerald-500/10 border border-emerald-500/20',
                ];
            case 'processing':
                return [
                    'statusCode' => 'processing',
                    'status' => 'Обробляється',
                    'statusIcon' => 'hourglass_empty',
                    'statusClass' => 'text-blue-550 bg-blue-550/10 border border-blue-550/20',
                ];
            case 'packed':
                return [
                    'statusCode' => 'packed',
                    'status' => 'Запаковано',
                    'statusIcon' => 'inventory_2',
                    'statusClass' => 'text-cyan-500 bg-cyan-500/10 border border-cyan-500/20',
                ];
            case 'pending':
            default:
                return [
                    'statusCode' => 'pending',
                    'status' => 'В обробці',
                    'statusIcon' => 'hourglass_empty',
                    'statusClass' => 'text-amber-505 bg-amber-500/10 border border-amber-500/20',
                ];
        }
    }

    protected function generateTrackingSteps(string $statusCode, $createdAt, $updatedAt): array
    {
        $createdStr = $createdAt->format('d.m.Y H:i');
        $updatedStr = $updatedAt->format('d.m.Y H:i');

        if ($statusCode === 'cancelled' || $statusCode === 'refunded') {
            $name = $statusCode === 'refunded' ? 'Повернуто кошти' : 'Скасовано';

            return [
                ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
                ['name' => $name, 'date' => $updatedStr, 'done' => true],
            ];
        }

        if ($statusCode === 'delivered' || $statusCode === 'completed') {
            $name = $statusCode === 'completed' ? 'Виконано' : 'Доставлено';

            return [
                ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
                ['name' => 'Обробка та комплектування', 'date' => $createdStr, 'done' => true],
                ['name' => 'Передано кур\'єру', 'date' => $updatedStr, 'done' => true],
                ['name' => 'Доставка отримувачу', 'date' => $updatedStr, 'done' => true],
                ['name' => $name, 'date' => $updatedStr, 'done' => true],
            ];
        }

        if ($statusCode === 'shipped') {
            return [
                ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
                ['name' => 'Обробка та комплектування', 'date' => $createdStr, 'done' => true],
                ['name' => 'Передано кур\'єру', 'date' => $updatedStr, 'done' => true],
                ['name' => 'Доставка отримувачу', 'date' => 'Очікується найближчим часом', 'done' => false],
                ['name' => 'Доставлено', 'date' => 'Очікується', 'done' => false],
            ];
        }

        // pending_payment, paid, processing, packed, pending / default
        $stageText = 'В процесі';
        if ($statusCode === 'pending_payment') {
            $stageText = 'Очікує оплати';
        } elseif ($statusCode === 'paid') {
            $stageText = 'Оплачено';
        } elseif ($statusCode === 'packed') {
            $stageText = 'Запаковано';
        }

        return [
            ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
            ['name' => 'Обробка та комплектування', 'date' => $stageText, 'done' => $statusCode !== 'pending_payment'],
            ['name' => 'Передано кур\'єру', 'date' => 'Очікується', 'done' => false],
            ['name' => 'Доставлено', 'date' => 'Очікується', 'done' => false],
        ];
    }

    public function cancelOrder(Request $request, int $id, UpdateAdminOrderStatusAction $action): JsonResponse
    {
        $user = $request->user();
        $order = Order::find($id);

        if (! $order) {
            return self::failedResponse('Замовлення не знайдено', 404);
        }

        if ($order->user_id !== $user->id && $order->customer_email !== $user->email) {
            return self::failedResponse('У вас немає доступу до цього замовлення', 403);
        }

        // Cancellable statuses: pending_payment, paid, processing, packed, pending
        if (in_array($order->status, ['shipped', 'delivered', 'completed', 'refunded'])) {
            return self::failedResponse('Це замовлення вже відправлено або виконано і його не можна скасувати.', 422);
        }

        if ($order->status === 'cancelled') {
            return self::failedResponse('Це замовлення вже скасовано.', 400);
        }

        $action->execute($id, new UpdateOrderStatusDto(
            status: 'cancelled',
            carrier: null,
            trackingNumber: null
        ));

        return self::successfulResponse('Замовлення успішно скасовано.');
    }
}
