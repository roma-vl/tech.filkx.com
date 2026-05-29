<?php

namespace App\Api\Admin\Requests\SupportSnippet;

use Illuminate\Foundation\Http\FormRequest;

class CreateSupportSnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }
}
