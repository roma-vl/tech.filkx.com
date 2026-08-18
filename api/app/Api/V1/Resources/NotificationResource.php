<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationResource',
    title: 'Notification Resource',
)]
class NotificationResource extends JsonResource
{
    #[OA\Property(
        property: 'id',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'userId',
        type: 'integer',
        nullable: true,
        description: 'Null for a broadcast (all-users) notification',
    )]
    #[OA\Property(
        property: 'title',
        type: 'string',
    )]
    #[OA\Property(
        property: 'content',
        type: 'string',
    )]
    #[OA\Property(
        property: 'type',
        type: 'string',
    )]
    #[OA\Property(
        property: 'link',
        type: 'string',
        nullable: true,
    )]
    #[OA\Property(
        property: 'readAt',
        type: 'string',
        format: 'date-time',
        nullable: true,
    )]
    #[OA\Property(
        property: 'createdAt',
        type: 'string',
        format: 'date-time',
        nullable: true,
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'link' => $this->link,
            'readAt' => $this->read_at ? $this->read_at->toIso8601String() : null,
            'createdAt' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
