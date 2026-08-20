<?php

namespace Tests\Unit\Actions\Admin\Team;

use App\Api\Admin\Actions\Team\ListInvitableRolesAction;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListInvitableRolesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListInvitableRolesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListInvitableRolesAction::class);
    }

    public function test_execute_returns_all_roles_ordered_by_name(): void
    {
        Role::query()->delete();
        Role::create(['name' => 'Viewer', 'slug' => 'viewer', 'scope' => 'global']);
        Role::create(['name' => 'Editor', 'slug' => 'editor', 'scope' => 'global']);

        $roles = $this->action->execute();

        $this->assertSame(['Editor', 'Viewer'], $roles->pluck('name')->all());
    }
}
