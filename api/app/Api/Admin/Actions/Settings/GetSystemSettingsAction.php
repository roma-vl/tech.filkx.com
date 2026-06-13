<?php

namespace App\Api\Admin\Actions\Settings;

use App\Models\Setting;

class GetSystemSettingsAction
{
    private const DEFAULTS = [
        'general' => [
            'platform_name' => ['value' => '', 'type' => 'string'],
            'support_email' => ['value' => '', 'type' => 'string'],
            'allow_registration' => ['value' => 'true', 'type' => 'boolean'],
        ],
        'shop' => [
            'currency' => ['value' => 'UAH', 'type' => 'string'],
            'default_tax_rate' => ['value' => '20', 'type' => 'float'],
            'min_order_amount' => ['value' => '0', 'type' => 'float'],
            'allow_guest_checkout' => ['value' => 'true', 'type' => 'boolean'],
            'free_shipping_threshold' => ['value' => '0', 'type' => 'float'],
        ],
        'security' => [
            'rate_limiting' => ['value' => 'true', 'type' => 'boolean'],
        ],
    ];

    public function execute(): array
    {
        $stored = Setting::all()->keyBy('key');
        $result = [];

        foreach (self::DEFAULTS as $group => $keys) {
            foreach ($keys as $key => $meta) {
                $record = $stored->get($key);
                $raw = $record ? $record->value : $meta['value'];

                $result[$group][$key] = match ($meta['type']) {
                    'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $raw,
                    'float' => (float) $raw,
                    default => $raw,
                };
            }
        }

        return ['settings' => $result];
    }
}
