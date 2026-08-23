<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'badge' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'imagePath' => 'required|string',
            'isActive' => 'boolean',
            'sortOrder' => 'nullable|integer|min:0',
            'productIds' => 'nullable|array',
            'productIds.*' => 'integer|exists:products,id',
        ];
    }
}
