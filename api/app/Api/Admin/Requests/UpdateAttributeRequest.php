<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\AttributeTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attributeId = $this->route('id');

        return [
            'code' => ['required', 'string', Rule::unique('attributes', 'code')->ignore($attributeId)],
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'type' => ['required', 'string', Rule::in(AttributeTypeEnum::values())],
            'values' => 'nullable|array',
            'categoryIds' => 'nullable|array',
            'categoryIds.*' => 'integer|exists:categories,id',
        ];
    }
}
