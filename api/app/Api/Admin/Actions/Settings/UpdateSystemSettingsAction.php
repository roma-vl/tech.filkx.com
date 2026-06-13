<?php

namespace App\Api\Admin\Actions\Settings;

use App\Models\AuditLog;
use App\Models\Setting;

class UpdateSystemSettingsAction
{
    private const KEY_TYPES = [
        'allow_registration' => ['group' => 'general', 'type' => 'boolean'],
        'platform_name' => ['group' => 'general', 'type' => 'string'],
        'support_email' => ['group' => 'general', 'type' => 'string'],
        'currency' => ['group' => 'shop', 'type' => 'string'],
        'default_tax_rate' => ['group' => 'shop', 'type' => 'float'],
        'min_order_amount' => ['group' => 'shop', 'type' => 'float'],
        'allow_guest_checkout' => ['group' => 'shop', 'type' => 'boolean'],
        'free_shipping_threshold' => ['group' => 'shop', 'type' => 'float'],
        'rate_limiting' => ['group' => 'security', 'type' => 'boolean'],
    ];

    public function execute(array $settings, string $ip, string $userAgent): void
    {
        foreach ($settings as $key => $value) {
            $meta = self::KEY_TYPES[$key] ?? ['group' => 'general', 'type' => 'string'];

            Setting::set($key, $value, $meta['group'], $meta['type']);
        }

        AuditLog::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => auth()->id(),
            'action' => 'settings.updated',
            'domain' => 'admin',
            'message' => 'System settings updated: ' . implode(', ', array_keys($settings)),
            'payload' => array_keys($settings),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }
}
