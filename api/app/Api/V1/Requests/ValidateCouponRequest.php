<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'      => 'required|string',
            'cartTotal' => 'required|numeric|min:0',
        ];
    }
}
