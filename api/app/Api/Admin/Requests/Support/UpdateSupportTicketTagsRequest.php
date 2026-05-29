<?php

namespace App\Api\Admin\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupportTicketTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tags' => ['nullable', 'array'],
        ];
    }
}
