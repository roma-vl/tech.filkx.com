<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\DiscountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', 'string', Rule::in(DiscountTypeEnum::values())],
            'amount' => 'required|numeric|min:0',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'isActive' => 'boolean',
        ];
    }
}
