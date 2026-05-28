<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdminSupportMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'userName' => $this->user?->name,
            'userAvatar' => $this->user?->avatar_path
                ? Storage::disk('public')->url($this->user?->avatar_path)
                : null,
            'message' => $this->message,
            'isAdmin' => (bool) $this->is_admin,
            'filePath' => $this->file_path,
            'fileUrl' => $this->file_path ? Storage::disk('public')->url($this->file_path) : null,
            'fileType' => $this->file_type,
            'fileName' => $this->file_name,
            'fileSize' => $this->file_size,
            'isAi' => (bool) $this->is_ai,
            'isInternal' => (bool) $this->is_internal,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'readAt' => $this->read_at?->toIso8601String(),
        ];
    }
}
