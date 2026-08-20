<?php

namespace App\Api\Admin\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nameUk' => 'required|string|max:100',
            'nameEn' => 'required|string|max:100',
        ];
    }
}
