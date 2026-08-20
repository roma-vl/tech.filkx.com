<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchDeliveryCitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => 'required|string|min:2|max:100',
        ];
    }
}
