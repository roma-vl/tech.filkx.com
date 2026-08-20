<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\UpdateRoleAction;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateRoleActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateRoleAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateRoleAction::class);
    }

    public function test_execute_updates_the_name_and_regenerates_the_slug(): void
    {
        $role = Role::create(['name' => 'Old Name', 'slug' => 'old-name', 'scope' => 'global']);

        $result = $this->action->execute($role->id, ['name' => 'New Name']);

        $this->assertSame('New Name', $result->name);
        $this->assertSame('new-name', $result->slug);
    }

    public function test_execute_syncs_permissions_when_given(): void
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'scope' => 'global']);
        Permission::create(['name' => 'View', 'slug' => 'reports.view', 'resource' => 'reports', 'action' => 'view']);

        $result = $this->action->execute($role->id, ['permissions' => ['reports.view']]);

        $this->assertTrue($result->permissions()->where('slug', 'reports.view')->exists());
    }

    public function test_execute_throws_not_found_for_an_unknown_role(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, ['name' => 'X']);
    }
}
