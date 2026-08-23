<?php

namespace App\Api\Admin\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nameUk' => 'required|string|max:255',
            'nameEn' => 'required|string|max:255',
            'descriptionUk' => 'nullable|string',
            'descriptionEn' => 'nullable|string',
            'order' => 'nullable|integer',
        ];
    }
}
