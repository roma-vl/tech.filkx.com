<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LedgerFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer',
            'type' => 'nullable|string|in:charge,refund',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ];
    }
}
