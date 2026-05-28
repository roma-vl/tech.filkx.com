<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="scope", type="string"),
 *     @OA\Property(property="isSystem", type="boolean"),
 *     @OA\Property(property="usersCount", type="integer"),
 *     @OA\Property(property="createdAt", type="string")
 * )
 */
class RoleResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'scope' => $this->scope,
            'isSystem' => $this->is_system,
            'usersCount' => $this->users_count ?? $this->users()->count(),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
