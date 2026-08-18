<?php

namespace App\Api\V1\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SupportMessageResource',
    title: 'Support Message Resource',
)]
class SupportMessageResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'message', type: 'string', nullable: true)]
    #[OA\Property(property: 'filePath', type: 'string', nullable: true)]
    #[OA\Property(property: 'fileType', type: 'string', nullable: true)]
    #[OA\Property(property: 'fileName', type: 'string', nullable: true)]
    #[OA\Property(property: 'fileSize', type: 'integer', nullable: true)]
    #[OA\Property(property: 'isAdmin', type: 'boolean')]
    #[OA\Property(property: 'createdAt', type: 'string', format: 'date-time')]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'filePath' => $this->file_path,
            'fileType' => $this->file_type,
            'fileName' => $this->file_name,
            'fileSize' => $this->file_size,
            'isAdmin' => $this->is_admin,
            'user' => $this->whenLoaded('user'),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
