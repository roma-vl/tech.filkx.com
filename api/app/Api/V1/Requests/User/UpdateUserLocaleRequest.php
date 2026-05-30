<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'in:uk,en,ua,ukrainian,english'],
        ];
    }
}
