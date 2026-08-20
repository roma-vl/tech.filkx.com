<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titleUk' => ['required', 'string', 'max:500'],
            'titleEn' => ['required', 'string', 'max:500'],
            'contentUk' => ['required', 'string'],
            'contentEn' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}
