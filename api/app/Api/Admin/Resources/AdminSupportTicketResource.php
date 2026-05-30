<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdminSupportTicketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->avatar_path
                    ? Storage::disk('public')->url($this->user?->avatar_path)
                    : null,
            ],
            'subject' => $this->subject,
            'status' => $this->status,
            'tags' => $this->tags ?? [],
            // Force calculation to ensure accuracy
            'unreadCount' => $this->unreadMessagesForAdmin()->count(),
            'lastMessage' => new AdminSupportMessageResource($this->whenLoaded('lastMessage')),
            'messages' => AdminSupportMessageResource::collection($this->whenLoaded('messages')),
            'handled_by' => $this->handled_by,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
