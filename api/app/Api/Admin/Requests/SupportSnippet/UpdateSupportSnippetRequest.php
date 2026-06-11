<?php

namespace App\Api\Admin\Requests\SupportSnippet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupportSnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ];
    }
}
