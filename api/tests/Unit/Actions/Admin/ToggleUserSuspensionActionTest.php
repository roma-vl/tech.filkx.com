<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\ToggleUserSuspensionAction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleUserSuspensionActionTest extends TestCase
{
    use RefreshDatabase;

    private ToggleUserSuspensionAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ToggleUserSuspensionAction::class);
    }

    public function test_execute_suspends_an_active_user(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $result = $this->action->execute($user->id, '127.0.0.1', 'PHPUnit');

        $this->assertSame('suspended', $result->status);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'suspended']);
    }

    public function test_execute_reactivates_a_suspended_user(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        $result = $this->action->execute($user->id, '127.0.0.1', 'PHPUnit');

        $this->assertSame('active', $result->status);
    }

    public function test_execute_works_on_soft_deleted_users(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->delete();

        $result = $this->action->execute($user->id, '127.0.0.1', 'PHPUnit');

        $this->assertSame('suspended', $result->status);
    }

    public function test_execute_records_an_audit_log_entry(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->action->execute($user->id, '10.0.0.9', 'PHPUnit-Agent');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.updated',
            'domain' => 'team',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'ip_address' => '10.0.0.9',
            'user_agent' => 'PHPUnit-Agent',
        ]);
    }

    public function test_execute_throws_for_an_unknown_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, '127.0.0.1', 'PHPUnit');
    }
}
