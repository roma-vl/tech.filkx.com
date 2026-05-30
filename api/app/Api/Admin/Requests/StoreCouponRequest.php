<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\DiscountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:coupons,code',
            'type' => ['required', 'string', Rule::in(DiscountTypeEnum::values())],
            'amount' => 'required|numeric|min:0',
            'usageLimit' => 'nullable|integer|min:1',
            'expiresAt' => 'nullable|date',
            'isActive' => 'boolean',
        ];
    }
}
