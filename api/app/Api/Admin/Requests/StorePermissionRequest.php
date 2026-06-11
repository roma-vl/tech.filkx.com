<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'resource' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
    }
}
