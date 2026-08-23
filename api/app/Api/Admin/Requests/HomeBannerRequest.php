<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomeBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'badge' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            // Optional: a banner image can already carry its own baked-in
            // design/copy, in which case no text is overlaid on top of it.
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'imagePath' => 'required|string',
            'buttonLabel' => 'nullable|string|max:100',
            'linkType' => ['required', Rule::in(['catalog', 'category', 'product', 'promo', 'url'])],
            'linkValue' => 'nullable|string|max:2048',
            'isActive' => 'boolean',
            'sortOrder' => 'nullable|integer|min:0',
        ];
    }
}
