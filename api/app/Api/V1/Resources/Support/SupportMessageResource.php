<?php

namespace App\Api\V1\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'message'   => $this->message,
            'filePath'  => $this->file_path,
            'fileType'  => $this->file_type,
            'fileName'  => $this->file_name,
            'fileSize'  => $this->file_size,
            'isAdmin'   => $this->is_admin,
            'user'      => $this->whenLoaded('user'),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
