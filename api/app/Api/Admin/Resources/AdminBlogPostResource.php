<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminBlogPost',
    title: 'Admin Blog Post Resource',
)]
class AdminBlogPostResource extends JsonResource
{
    public function __construct($resource, protected bool $withContent = false)
    {
        parent::__construct($resource);
    }

    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'slug', type: 'string')]
    #[OA\Property(property: 'titleUk', type: 'string')]
    #[OA\Property(property: 'titleEn', type: 'string')]
    #[OA\Property(property: 'excerptUk', type: 'string')]
    #[OA\Property(property: 'excerptEn', type: 'string')]
    #[OA\Property(property: 'coverImage', type: 'string', nullable: true)]
    #[OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'])]
    #[OA\Property(property: 'views', type: 'integer')]
    #[OA\Property(property: 'publishedAt', type: 'string', format: 'date-time', nullable: true)]
    #[OA\Property(property: 'createdAt', type: 'string', format: 'date-time')]
    #[OA\Property(property: 'categoryId', type: 'integer', nullable: true)]
    #[OA\Property(property: 'categoryName', type: 'string', nullable: true)]
    #[OA\Property(property: 'authorName', type: 'string', nullable: true)]
    #[OA\Property(property: 'contentUk', type: 'string')]
    #[OA\Property(property: 'contentEn', type: 'string')]
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'slug' => $this->slug,
            'titleUk' => $this->title['uk'] ?? '',
            'titleEn' => $this->title['en'] ?? '',
            'excerptUk' => $this->excerpt['uk'] ?? '',
            'excerptEn' => $this->excerpt['en'] ?? '',
            'coverImage' => $this->cover_image,
            'status' => $this->status,
            'views' => $this->views,
            'publishedAt' => $this->published_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'categoryId' => $this->blog_category_id,
            'categoryName' => $this->category ? ($this->category->name['uk'] ?? $this->category->name['en'] ?? '') : null,
            'authorName' => $this->author ? $this->author->name : null,
            'tags' => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'nameUk' => $tag->name['uk'] ?? '',
                'nameEn' => $tag->name['en'] ?? '',
                'slug' => $tag->slug,
            ])->values(),
        ];

        if ($this->withContent) {
            $data['contentUk'] = $this->content['uk'] ?? '';
            $data['contentEn'] = $this->content['en'] ?? '';
        }

        return $data;
    }
}
