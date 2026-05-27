<?php

namespace App\Api\V1\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User Resource",
 *     description="User details response"
 * )
 */
class UserResource extends JsonResource
{
    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="name", type="string", example="John Doe")
     * @OA\Property(property="email", type="string", format="email", example="john@example.com")
     * @OA\Property(property="locale", type="string", example="en", enum={"en", "uk"})
     * @OA\Property(property="timezone", type="string", example="UTC")
     * @OA\Property(property="emailVerifiedAt", type="string", format="date-time", nullable=true)
     * @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true, description="Account deletion timestamp (null if active)")
     * @OA\Property(property="avatarUrl", type="string", nullable=true, example="https://example.com/avatar.jpg")
     * @OA\Property(property="description", type="string", nullable=true, example="User bio")
     * @OA\Property(property="status", type="string", example="active")
     * @OA\Property(property="createdAt", type="string", format="date-time")
     * @OA\Property(
     *     property="roles",
     *     type="array",
     *
     *     @OA\Items(type="string", example="user")
     * )
     *
     * @OA\Property(
     *     property="permissions",
     *     type="array",
     *
     *     @OA\Items(type="string", example="stream.start")
     * )
     *
     * @OA\Property(
     *     property="subscription",
     *     type="object",
     *     nullable=true,
     *     @OA\Property(property="plan", type="string", example="pro"),
     *     @OA\Property(property="isTrial", type="boolean", example=false),
     *     @OA\Property(property="trialEndsAt", type="string", format="date-time", nullable=true)
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'emailChangePending' => $this->email_change_pending ?? false,
            'hasPassword' => ! empty($this->password),
            'oauth_accounts' => $this->oauthAccounts->map(fn ($acc) => [
                'provider' => $acc->provider,
                'created_at' => $acc->created_at,
            ]),
            'emailVerifiedAt' => $this->email_verified_at,
            'deletedAt' => $this->deleted_at, // Add deleted_at for restoration detection
            'avatarUrl' => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
            'description' => $this->getDescriptionAttribute() ?? null,
            'status' => $this->status,
            'hasReferrer' => $this->referral()->exists(),
            'referrer' => $this->when($this->referral()->exists(), function () {
                $referral = $this->referral;

                return [
                    'code' => $referral->affiliate->referral_code ?? null,
                    'name' => $referral->affiliate->user->name ?? null,
                    'setAt' => $referral->created_at?->toIso8601String(),
                ];
            }),
            'createdAt' => $this->created_at->toIso8601String(),
            'roles' => $this->roles->pluck('slug'),
            'permissions' => $this->getPermissions(),
            'subscription' => [
                'plan' => $this->subscription?->plan?->slug,
                'isTrial' => $this->subscription?->plan?->is_trial,
                'trialEndsAt' => $this->subscription?->trial_ends_at
                    ? $this->subscription->trial_ends_at->setTimezone('UTC')->toIso8601String()
                    : null,
            ],
        ];
    }
}
