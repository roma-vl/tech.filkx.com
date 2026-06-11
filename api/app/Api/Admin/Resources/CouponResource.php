<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'usageLimit' => $this->usage_limit,
            'usedCount' => $this->used_count,
            'expiresAt' => $this->expires_at ? $this->expires_at->toIso8601String() : null,
            'isActive' => (bool) $this->is_active,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
