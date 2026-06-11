<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sessionId' => $this->sessionId,
            'items' => CartItemResource::collection($this->items),
            'total' => (float) $this->total,
        ];
    }
}
