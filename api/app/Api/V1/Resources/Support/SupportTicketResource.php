<?php

namespace App\Api\V1\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'status' => $this->status,
            'read_at' => $this->read_at ? $this->read_at->toIso8601String() : null,
            'messages' => SupportMessageResource::collection($this->whenLoaded('messages')),
            'publicMessages' => SupportMessageResource::collection($this->whenLoaded('publicMessages')),
            'user' => $this->whenLoaded('user'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
