<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="resource", type="string"),
 *     @OA\Property(property="action", type="string"),
 *     @OA\Property(property="isSystem", type="boolean")
 * )
 */
class PermissionResource extends JsonResource
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
            'resource' => $this->resource,
            'action' => $this->action,
            'isSystem' => $this->is_system,
        ];
    }
}
