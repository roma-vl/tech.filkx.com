<?php

namespace Tests\Unit\Actions\Admin\Settings;

use App\Api\Admin\Actions\Settings\GetSystemSettingsAction;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSystemSettingsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetSystemSettingsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetSystemSettingsAction::class);
    }

    public function test_execute_returns_typed_defaults_when_nothing_is_stored(): void
    {
        $result = $this->action->execute();

        $settings = $result['settings'];
        $this->assertSame('', $settings['general']['platform_name']);
        $this->assertSame('', $settings['general']['support_email']);
        $this->assertTrue($settings['general']['allow_registration']);
        $this->assertSame('UAH', $settings['shop']['currency']);
        $this->assertSame(20.0, $settings['shop']['default_tax_rate']);
        $this->assertSame(0.0, $settings['shop']['min_order_amount']);
        $this->assertTrue($settings['shop']['allow_guest_checkout']);
        $this->assertSame(0.0, $settings['shop']['free_shipping_threshold']);
        $this->assertTrue($settings['security']['rate_limiting']);
    }

    public function test_execute_overrides_defaults_with_stored_values_and_casts_by_type(): void
    {
        Setting::set('platform_name', 'FilkxTech', 'general', 'string');
        Setting::set('default_tax_rate', '15.5', 'shop', 'float');
        Setting::set('allow_registration', false, 'general', 'boolean');

        $result = $this->action->execute();
        $settings = $result['settings'];

        $this->assertSame('FilkxTech', $settings['general']['platform_name']);
        $this->assertSame(15.5, $settings['shop']['default_tax_rate']);
        $this->assertIsFloat($settings['shop']['default_tax_rate']);
        $this->assertFalse($settings['general']['allow_registration']);
    }

    public function test_execute_ignores_stored_settings_not_in_the_known_key_list(): void
    {
        Setting::set('unrelated_key', 'some-value', 'general', 'string');

        $result = $this->action->execute();

        $this->assertArrayNotHasKey('unrelated_key', $result['settings']['general']);
    }
}
