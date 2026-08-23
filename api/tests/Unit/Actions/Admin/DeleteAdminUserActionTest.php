<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\DeleteAdminUserAction;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdminUserActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAdminUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteAdminUserAction::class);
    }

    public function test_execute_soft_deletes_a_non_admin_user(): void
    {
        $user = User::factory()->create();

        $this->action->execute($user->id, '127.0.0.1', 'PHPUnit');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_execute_records_an_audit_log_entry(): void
    {
        $user = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $this->action->execute($user->id, '10.0.0.5', 'PHPUnit-Agent');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.deleted',
            'domain' => 'team',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'ip_address' => '10.0.0.5',
            'user_agent' => 'PHPUnit-Agent',
        ]);
    }

    public function test_execute_throws_when_the_user_has_the_admin_role(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->roles()->attach($adminRole->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot delete admin user');

        $this->action->execute($user->id, '127.0.0.1', 'PHPUnit');
    }

    public function test_execute_leaves_the_admin_user_intact_when_deletion_is_blocked(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->roles()->attach($adminRole->id);

        try {
            $this->action->execute($user->id, '127.0.0.1', 'PHPUnit');
            $this->fail('Expected an exception to be thrown.');
        } catch (Exception) {
            // expected
        }

        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_execute_throws_when_the_user_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, '127.0.0.1', 'PHPUnit');
    }
}
