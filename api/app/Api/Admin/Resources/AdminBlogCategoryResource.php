<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminBlogCategory',
    title: 'Admin Blog Category Resource',
)]
class AdminBlogCategoryResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'slug', type: 'string')]
    #[OA\Property(property: 'nameUk', type: 'string')]
    #[OA\Property(property: 'nameEn', type: 'string')]
    #[OA\Property(property: 'descriptionUk', type: 'string')]
    #[OA\Property(property: 'descriptionEn', type: 'string')]
    #[OA\Property(property: 'order', type: 'integer')]
    #[OA\Property(property: 'postsCount', type: 'integer')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'nameUk' => $this->name['uk'] ?? '',
            'nameEn' => $this->name['en'] ?? '',
            'descriptionUk' => $this->description['uk'] ?? '',
            'descriptionEn' => $this->description['en'] ?? '',
            'order' => $this->order,
            'postsCount' => $this->posts_count ?? 0,
        ];
    }
}
