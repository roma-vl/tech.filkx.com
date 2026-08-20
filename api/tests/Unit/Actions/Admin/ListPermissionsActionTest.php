<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\ListPermissionsAction;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPermissionsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListPermissionsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListPermissionsAction::class);
    }

    public function test_execute_returns_all_permissions(): void
    {
        $seeded = Permission::count();

        Permission::create(['name' => 'View', 'slug' => 'reports.view', 'resource' => 'reports', 'action' => 'view']);
        Permission::create(['name' => 'Edit', 'slug' => 'reports.edit', 'resource' => 'reports', 'action' => 'edit']);

        $result = $this->action->execute();

        $this->assertCount($seeded + 2, $result);
        $this->assertTrue($result->pluck('slug')->contains('reports.view'));
    }
}
