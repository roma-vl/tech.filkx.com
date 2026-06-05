<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'newsletter' => ['required', 'boolean'],
            'productUpdates' => ['required', 'boolean'],
            'marketingEmails' => ['required', 'boolean'],
        ];
    }
}
