<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'parentId' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
        ];
    }
}
