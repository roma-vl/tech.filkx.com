<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminBlogTag',
    title: 'Admin Blog Tag Resource',
)]
class AdminBlogTagResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'slug', type: 'string')]
    #[OA\Property(property: 'nameUk', type: 'string')]
    #[OA\Property(property: 'nameEn', type: 'string')]
    #[OA\Property(property: 'postsCount', type: 'integer')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'nameUk' => $this->name['uk'] ?? '',
            'nameEn' => $this->name['en'] ?? '',
            'postsCount' => $this->posts_count ?? 0,
        ];
    }
}
