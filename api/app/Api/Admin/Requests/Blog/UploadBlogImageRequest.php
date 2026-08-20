<?php

namespace App\Api\Admin\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UploadBlogImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|max:5120', // Max 5MB
        ];
    }
}
