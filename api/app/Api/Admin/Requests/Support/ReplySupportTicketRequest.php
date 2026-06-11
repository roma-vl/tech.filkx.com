<?php

namespace App\Api\Admin\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class ReplySupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required_without:file', 'string', 'nullable'],
            'file' => ['nullable', 'file', 'max:102400'], // 100MB
            'is_internal' => ['nullable', 'boolean'],
        ];
    }
}
