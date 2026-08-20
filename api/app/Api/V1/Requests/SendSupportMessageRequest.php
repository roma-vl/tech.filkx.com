<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSupportMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ];
    }
}
