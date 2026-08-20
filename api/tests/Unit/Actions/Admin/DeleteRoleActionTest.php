<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\DeleteRoleAction;
use App\Models\Role;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteRoleActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteRoleAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteRoleAction::class);
    }

    public function test_execute_deletes_a_non_system_role(): void
    {
        $role = Role::create(['name' => 'Temp', 'slug' => 'temp', 'scope' => 'global']);

        $result = $this->action->execute($role->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_execute_throws_when_the_role_is_a_system_role(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot delete system role');

        $this->action->execute($adminRole->id);
    }

    public function test_execute_throws_not_found_for_an_unknown_role(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
