<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\CreateRoleAction;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRoleActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateRoleAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateRoleAction::class);
    }

    public function test_execute_creates_a_role_with_a_slug_derived_from_the_name(): void
    {
        $role = $this->action->execute(['name' => 'Store Manager']);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Store Manager',
            'slug' => 'store-manager',
        ]);
    }

    public function test_execute_attaches_the_given_permissions(): void
    {
        Permission::create(['name' => 'View', 'slug' => 'reports.view', 'resource' => 'reports', 'action' => 'view']);

        $role = $this->action->execute([
            'name' => 'Analyst',
            'permissions' => ['reports.view'],
        ]);

        $this->assertTrue($role->permissions()->where('slug', 'reports.view')->exists());
    }
}
