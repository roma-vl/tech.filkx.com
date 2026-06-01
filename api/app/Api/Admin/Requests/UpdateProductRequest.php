<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_uk'          => 'required|string',
            'name_en'          => 'required|string',
            'description_uk'   => 'nullable|string',
            'description_en'   => 'nullable|string',
            'status'           => ['required', 'string', Rule::in(ProductStatusEnum::values())],
            'is_hot'           => 'nullable|boolean',
            'is_recommended'   => 'nullable|boolean',
            'brand_id'         => 'nullable|exists:brands,id',
            'category_id'      => 'required|exists:categories,id',
            'variants'         => 'required|array|min:1',
            'variants.*.id'    => 'nullable|integer',
            'variants.*.sku'   => 'required|string',
            'variants.*.price'     => 'required|numeric|min:0',
            'variants.*.old_price' => 'nullable|numeric|min:0',
            'variants.*.stock'     => 'required|integer|min:0',
            'variants.*.weight'    => 'nullable|numeric|min:0',
            'variants.*.images'    => 'required|array',
            'variants.*.attributes' => 'nullable|array',
        ];
    }
}
