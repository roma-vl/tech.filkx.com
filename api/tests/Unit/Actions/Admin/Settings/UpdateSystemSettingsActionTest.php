<?php

namespace Tests\Unit\Actions\Admin\Settings;

use App\Api\Admin\Actions\Settings\UpdateSystemSettingsAction;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateSystemSettingsActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateSystemSettingsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateSystemSettingsAction::class);
    }

    public function test_execute_persists_settings_with_the_known_group_and_type(): void
    {
        $this->action->execute(
            ['currency' => 'USD', 'allow_registration' => false],
            '127.0.0.1',
            'PHPUnit'
        );

        $this->assertDatabaseHas('settings', [
            'key' => 'currency',
            'value' => 'USD',
            'group' => 'shop',
            'type' => 'string',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'allow_registration',
            'value' => 'false',
            'group' => 'general',
            'type' => 'boolean',
        ]);
    }

    public function test_execute_defaults_unknown_keys_to_general_string(): void
    {
        $this->action->execute(['custom_flag' => 'yes'], '127.0.0.1', 'PHPUnit');

        $this->assertDatabaseHas('settings', [
            'key' => 'custom_flag',
            'value' => 'yes',
            'group' => 'general',
            'type' => 'string',
        ]);
    }

    public function test_execute_updates_an_existing_setting_instead_of_duplicating_it(): void
    {
        Setting::set('currency', 'UAH', 'shop', 'string');

        $this->action->execute(['currency' => 'EUR'], '127.0.0.1', 'PHPUnit');

        $this->assertSame(1, Setting::where('key', 'currency')->count());
        $this->assertSame('EUR', Setting::where('key', 'currency')->value('value'));
    }

    public function test_execute_writes_an_audit_log_entry_with_the_changed_keys(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->action->execute(
            ['currency' => 'USD', 'platform_name' => 'FilkxTech'],
            '10.0.0.1',
            'PHPUnit-Agent'
        );

        $log = AuditLog::sole();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('settings.updated', $log->action);
        $this->assertSame('admin', $log->domain);
        $this->assertSame('System settings updated: currency, platform_name', $log->message);
        $this->assertSame(['currency', 'platform_name'], $log->payload);
        $this->assertSame('10.0.0.1', $log->ip_address);
        $this->assertSame('PHPUnit-Agent', $log->user_agent);
    }

    public function test_execute_writes_an_audit_log_entry_with_a_null_user_id_when_unauthenticated(): void
    {
        $this->action->execute(['currency' => 'USD'], '127.0.0.1', 'PHPUnit');

        $log = AuditLog::sole();
        $this->assertNull($log->user_id);
    }
}
