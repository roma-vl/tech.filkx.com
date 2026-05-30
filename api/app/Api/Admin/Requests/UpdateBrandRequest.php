<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('id');

        return [
            'name' => ['required', 'string', Rule::unique('brands', 'name')->ignore($brandId)],
            'logoPath' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
