<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => 'required',
            'quantity' => 'nullable|integer|min:1',
        ];
    }
}
