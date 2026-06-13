<?php

namespace App\Api\Admin\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.platform_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'settings.support_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'settings.allow_registration' => ['sometimes', 'boolean'],
            'settings.currency' => ['sometimes', 'string', 'in:UAH,USD,EUR,GBP,PLN'],
            'settings.default_tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'settings.min_order_amount' => ['sometimes', 'numeric', 'min:0'],
            'settings.allow_guest_checkout' => ['sometimes', 'boolean'],
            'settings.free_shipping_threshold' => ['sometimes', 'numeric', 'min:0'],
            'settings.rate_limiting' => ['sometimes', 'boolean'],
        ];
    }
}
