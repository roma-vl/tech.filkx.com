<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
            ],
            'amountMinor' => (int) ($this->total_price * 100),
            'currency' => 'UAH',
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
