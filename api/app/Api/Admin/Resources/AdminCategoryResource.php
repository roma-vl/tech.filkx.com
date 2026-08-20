<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminCategory',
    title: 'Admin Category Resource',
)]
class AdminCategoryResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'parentId', type: 'integer', nullable: true)]
    #[OA\Property(property: 'parentName', type: 'string', nullable: true)]
    #[OA\Property(property: 'slug', type: 'string')]
    #[OA\Property(property: 'nameUk', type: 'string')]
    #[OA\Property(property: 'nameEn', type: 'string')]
    #[OA\Property(property: 'order', type: 'integer')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parentId' => $this->parent_id,
            'parentName' => $this->whenLoaded('parent', fn () => $this->parent
                ? ($this->parent->name['uk'] ?? $this->parent->name['en'] ?? '')
                : null),
            'slug' => $this->slug,
            'nameUk' => $this->name['uk'] ?? '',
            'nameEn' => $this->name['en'] ?? '',
            'order' => $this->order,
        ];
    }
}
