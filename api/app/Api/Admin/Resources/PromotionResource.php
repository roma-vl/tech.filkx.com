<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'startDate' => $this->start_date ? $this->start_date->toIso8601String() : null,
            'endDate' => $this->end_date ? $this->end_date->toIso8601String() : null,
            'isActive' => (bool) $this->is_active,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
