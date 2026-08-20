<?php

namespace App\Api\Admin\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titleUk' => 'required|string|max:500',
            'titleEn' => 'required|string|max:500',
            'contentUk' => 'required|string',
            'contentEn' => 'required|string',
            'excerptUk' => 'nullable|string|max:1000',
            'excerptEn' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published,archived',
            'categoryId' => 'nullable|exists:blog_categories,id',
            'tagIds' => 'nullable|array',
            'tagIds.*' => 'exists:blog_tags,id',
            'coverImage' => 'nullable|string',
            'publishedAt' => 'nullable|date',
        ];
    }
}
