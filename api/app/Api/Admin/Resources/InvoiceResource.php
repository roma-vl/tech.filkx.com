<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = 'draft';
        if ($this->status === 'completed') {
            $status = 'paid';
        } elseif ($this->status === 'pending') {
            $status = 'issued';
        } elseif ($this->status === 'cancelled') {
            $status = 'cancelled';
        }

        return [
            'invoiceNumber' => $this->order_number,
            'user' => $this->user ? [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
            ],
            'totalMinor' => (int) ($this->total_price * 100),
            'status' => $status,
            'currency' => 'UAH',
            'issuedAt' => $this->created_at->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
