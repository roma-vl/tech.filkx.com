<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'administrator']);
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$userId,
            'status' => 'sometimes|string|in:active,suspended',
            'featuresSnapshot' => 'sometimes|array',
            'subscriptionUsage' => 'sometimes|array',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,slug',
        ];
    }
}
