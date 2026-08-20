<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminPage',
    title: 'Admin Page Resource',
)]
class PageResource extends JsonResource
{
    public function __construct($resource, protected bool $withContent = false)
    {
        parent::__construct($resource);
    }

    #[OA\Property(
        property: 'id',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'slug',
        type: 'string',
    )]
    #[OA\Property(
        property: 'titleUk',
        type: 'string',
    )]
    #[OA\Property(
        property: 'titleEn',
        type: 'string',
    )]
    #[OA\Property(
        property: 'status',
        type: 'string',
        enum: ['draft', 'published'],
    )]
    #[OA\Property(
        property: 'createdAt',
        type: 'string',
        format: 'date-time',
    )]
    #[OA\Property(
        property: 'updatedAt',
        type: 'string',
        format: 'date-time',
    )]
    #[OA\Property(
        property: 'contentUk',
        type: 'string',
    )]
    #[OA\Property(
        property: 'contentEn',
        type: 'string',
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'titleUk' => $this->title['uk'] ?? '',
            'titleEn' => $this->title['en'] ?? '',
            'status' => $this->status,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
            'contentUk' => $this->when($this->withContent, fn () => $this->content['uk'] ?? ''),
            'contentEn' => $this->when($this->withContent, fn () => $this->content['en'] ?? ''),
        ];
    }
}
