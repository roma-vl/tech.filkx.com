<?php

namespace App\Api\V1\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'is_admin' => $this->is_admin,
            'user' => $this->whenLoaded('user'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
