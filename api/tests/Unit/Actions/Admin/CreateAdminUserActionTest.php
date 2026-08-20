<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\CreateAdminUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminUserActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateAdminUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateAdminUserAction::class);
    }

    public function test_execute_creates_an_active_user_with_a_hashed_password(): void
    {
        $user = $this->action->execute([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ], '127.0.0.1', 'PHPUnit');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'status' => 'active',
        ]);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_execute_records_an_audit_log_entry(): void
    {
        $user = $this->action->execute([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ], '10.0.0.1', 'PHPUnit-Agent');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.created',
            'domain' => 'team',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'PHPUnit-Agent',
        ]);
    }
}
