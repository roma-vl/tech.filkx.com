<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isCompleted = $this->status === 'completed';

        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
            ],
            'type' => $isCompleted ? 'charge' : 'refund',
            'amountMinor' => $isCompleted ? (int)($this->total_price * 100) : -(int)($this->total_price * 100),
            'currency' => 'UAH',
            'referenceType' => 'order',
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
