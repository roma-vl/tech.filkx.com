<?php

namespace App\Api\V1\Resources\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserActivityResource',
    title: 'User Activity Resource',
)]
class UserActivityResource extends JsonResource
{
    #[OA\Property(
        property: 'id',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'type',
        type: 'string',
        example: 'order.placed',
    )]
    #[OA\Property(
        property: 'subjectType',
        type: 'string',
        example: 'App\\Models\\Order',
    )]
    #[OA\Property(
        property: 'subjectId',
        type: 'integer',
    )]
    #[OA\Property(
        property: 'metadata',
        type: 'object',
        nullable: true,
    )]
    #[OA\Property(
        property: 'createdAt',
        type: 'string',
        format: 'date-time',
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->activity_type,
            'subjectType' => $this->subject_type,
            'subjectId' => $this->subject_id,
            'metadata' => $this->metadata,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
