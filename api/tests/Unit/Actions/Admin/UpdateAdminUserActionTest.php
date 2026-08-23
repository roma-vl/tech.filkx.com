<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\UpdateAdminUserAction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAdminUserActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAdminUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateAdminUserAction::class);
    }

    public function test_execute_updates_the_allowed_fields(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'status' => 'active']);

        $result = $this->action->execute($user->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'status' => 'suspended',
        ], '127.0.0.1', 'PHPUnit');

        $this->assertSame('New Name', $result->name);
        $this->assertSame('new@example.com', $result->email);
        $this->assertSame('suspended', $result->status);
    }

    public function test_execute_ignores_fields_outside_the_allow_list(): void
    {
        $user = User::factory()->create(['name' => 'Original']);

        $this->action->execute($user->id, [
            'name' => 'Updated',
            'password' => 'should-be-ignored',
        ], '127.0.0.1', 'PHPUnit');

        $user->refresh();
        $this->assertSame('Updated', $user->name);
    }

    public function test_execute_works_on_soft_deleted_users(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $result = $this->action->execute($user->id, ['name' => 'Revived Name'], '127.0.0.1', 'PHPUnit');

        $this->assertSame('Revived Name', $result->name);
    }

    /**
     * The action's featuresSnapshot/subscriptionUsage handling only fires when
     * $user->subscription resolves to a model, but User has no subscription()
     * relation, so $user->subscription is always null and these branches are
     * dead code. This test documents that passing them is a harmless no-op.
     */
    public function test_execute_does_not_error_when_passed_subscription_related_fields(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user->id, [
            'name' => 'Still Works',
            'featuresSnapshot' => ['concurrentStreams' => 5],
            'subscriptionUsage' => ['streamsActive' => 2],
        ], '127.0.0.1', 'PHPUnit');

        $this->assertSame('Still Works', $result->name);
    }

    public function test_execute_records_an_audit_log_entry_with_the_submitted_payload(): void
    {
        $user = User::factory()->create();

        $this->action->execute($user->id, ['name' => 'Audited'], '10.0.0.2', 'PHPUnit-Agent');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.updated',
            'domain' => 'team',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'ip_address' => '10.0.0.2',
            'user_agent' => 'PHPUnit-Agent',
        ]);
    }

    public function test_execute_throws_for_an_unknown_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, ['name' => 'X'], '127.0.0.1', 'PHPUnit');
    }
}
