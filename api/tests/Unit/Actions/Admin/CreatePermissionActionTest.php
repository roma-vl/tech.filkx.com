<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\CreatePermissionAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePermissionActionTest extends TestCase
{
    use RefreshDatabase;

    private CreatePermissionAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreatePermissionAction::class);
    }

    public function test_execute_creates_a_permission_from_the_given_data(): void
    {
        $permission = $this->action->execute([
            'name' => 'View reports',
            'slug' => 'reports.view',
            'resource' => 'reports',
            'action' => 'view',
        ]);

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'slug' => 'reports.view',
            'resource' => 'reports',
            'action' => 'view',
        ]);
    }
}
