<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchDeliveryWarehousesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cityRef' => 'required|string',
            'query' => 'nullable|string|max:100',
        ];
    }
}
