<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchAdminProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'categoryId' => 'nullable|integer',
            'brandId' => 'nullable|integer',
            'status' => ['nullable', 'string', Rule::in(['active', 'draft', 'hidden'])],
            'hot' => 'nullable|boolean',
            'recommended' => 'nullable|boolean',
            'hasImage' => ['nullable', 'string', Rule::in(['with', 'without'])],
            'stock' => ['nullable', 'string', Rule::in(['inStock', 'outOfStock'])],
            'sort' => ['nullable', 'string', Rule::in([
                'name-asc', 'name-desc', 'price-asc', 'price-desc', 'stock-asc', 'stock-desc',
            ])],
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1|max:100',
        ];
    }
}
