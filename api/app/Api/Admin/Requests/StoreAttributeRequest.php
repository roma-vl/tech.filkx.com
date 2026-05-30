<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\AttributeTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:attributes,code',
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'type' => ['required', 'string', Rule::in(AttributeTypeEnum::values())],
            'values' => 'nullable|array',
        ];
    }
}
