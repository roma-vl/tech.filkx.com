<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string',
            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|min:0',
            'discounts' => 'nullable|string|in:true,false,1,0',
            'in_stock' => 'nullable|string|in:true,false,1,0',
            'attrs' => 'nullable|array',
            'sort_by' => 'nullable|string|in:popularity,newest,price-asc,price-desc',
        ];
    }
}
