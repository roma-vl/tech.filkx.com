<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'userId'    => $this->user_id,
            'title'     => $this->title,
            'content'   => $this->content,
            'type'      => $this->type,
            'link'      => $this->link,
            'readAt'    => $this->read_at ? $this->read_at->toIso8601String() : null,
            'createdAt' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
