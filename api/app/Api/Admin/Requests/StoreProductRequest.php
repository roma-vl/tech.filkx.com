<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'descriptionUk' => 'nullable|string',
            'descriptionEn' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(ProductStatusEnum::values())],
            'isHot' => 'nullable|boolean',
            'isRecommended' => 'nullable|boolean',
            'brandId' => 'nullable|exists:brands,id',
            'categoryId' => 'required|exists:categories,id',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.oldPrice' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.images' => 'required|array',
            'variants.*.attributes' => 'nullable|array',
        ];
    }
}
