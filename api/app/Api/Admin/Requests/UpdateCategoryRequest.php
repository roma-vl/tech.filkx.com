<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = (int) $this->route('id');

        return [
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'parentId' => ['nullable', 'exists:categories,id', Rule::notIn([$categoryId])],
            'order' => 'nullable|integer',
        ];
    }
}
