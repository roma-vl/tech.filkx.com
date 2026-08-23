<?php

namespace App\Api\V1\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResource',
    title: 'User Resource',
    description: 'User details response',
)]
class UserResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    #[OA\Property(property: 'name', type: 'string', example: 'John Doe')]
    #[OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com')]
    #[OA\Property(property: 'locale', type: 'string', example: 'en', enum: ['en', 'uk'])]
    #[OA\Property(property: 'phone', type: 'string', nullable: true, example: '+380501234567')]
    #[OA\Property(property: 'language', type: 'string', nullable: true, example: 'uk')]
    #[OA\Property(property: 'timezone', type: 'string', example: 'UTC')]
    #[OA\Property(property: 'hasPassword', type: 'boolean', example: true)]
    #[OA\Property(
        property: 'oauthAccounts',
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'provider', type: 'string', example: 'google'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ],
            type: 'object',
        ),
    )]
    #[OA\Property(property: 'emailVerifiedAt', type: 'string', format: 'date-time', nullable: true)]
    #[OA\Property(property: 'deletedAt', type: 'string', format: 'date-time', nullable: true)]
    #[OA\Property(property: 'avatarUrl', type: 'string', nullable: true, example: 'https://example.com/avatar.jpg')]
    #[OA\Property(property: 'description', type: 'string', nullable: true, example: 'User bio')]
    #[OA\Property(property: 'status', type: 'string', example: 'active')]
    #[OA\Property(property: 'createdAt', type: 'string', format: 'date-time')]
    #[OA\Property(property: 'twoFactorEnabled', type: 'boolean', example: false)]
    #[OA\Property(
        property: 'roles',
        type: 'array',
        items: new OA\Items(type: 'string', example: 'user'),
    )]
    #[OA\Property(
        property: 'permissions',
        type: 'array',
        items: new OA\Items(type: 'string', example: 'stream.start'),
    )]
    #[OA\Property(property: 'addresses', type: 'array', items: new OA\Items(type: 'object'))]
    #[OA\Property(property: 'subscription', type: 'object', nullable: true, description: 'Subscription module not yet implemented')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'locale' => $this->locale,
            'phone' => $this->phone,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'hasPassword' => ! empty($this->password),
            'oauthAccounts' => $this->whenLoaded('oauthAccounts', fn () => $this->oauthAccounts->map(fn ($acc) => [
                'provider' => $acc->provider,
                'createdAt' => $acc->created_at,
            ])),
            'emailVerifiedAt' => $this->email_verified_at,
            'deletedAt' => $this->deleted_at,
            'avatarUrl' => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
            'description' => $this->getDescriptionAttribute() ?? null,
            'status' => $this->status,
            'createdAt' => $this->created_at->toIso8601String(),
            'twoFactorEnabled' => $this->hasTwoFactorEnabled(),
            'roles' => $this->relationLoaded('roles') ? $this->roles->pluck('slug') : $this->roles()->pluck('slug')->toArray(),
            'permissions' => $this->getPermissions(),
            'addresses' => $this->settings['addresses'] ?? [],
            // Subscription module not yet implemented
            'subscription' => null,
        ];
    }
}
