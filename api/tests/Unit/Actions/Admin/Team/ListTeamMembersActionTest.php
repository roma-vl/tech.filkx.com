<?php

namespace Tests\Unit\Actions\Admin\Team;

use App\Api\Admin\Actions\Team\ListTeamMembersAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamMembersActionTest extends TestCase
{
    use RefreshDatabase;

    private ListTeamMembersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListTeamMembersAction::class);
    }

    /**
     * The 'admin'/'owner' slugs are seeded by RolesAndPermissionsSeeder (via migration),
     * with a display `name` that doesn't match the slug (e.g. slug "admin" / name
     * "Administrator") — fetching by slug here doubles as regression coverage for that
     * name/slug mismatch.
     */
    private function seededRole(string $slug): Role
    {
        return Role::where('slug', $slug)->firstOrFail();
    }

    public function test_execute_only_returns_users_holding_an_admin_team_role(): void
    {
        $admin = $this->seededRole('admin');
        $customerRole = Role::create(['name' => 'Customer', 'slug' => 'zz-test-customer', 'scope' => 'global']);

        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($admin->id);

        $customer = User::factory()->create();
        $customer->roles()->attach($customerRole->id);

        $result = $this->action->execute();

        $this->assertTrue($result['team']->contains('id', $adminUser->id));
        $this->assertFalse($result['team']->contains('id', $customer->id));
    }

    public function test_execute_returns_total_and_owner_counts(): void
    {
        $admin = $this->seededRole('admin');
        $owner = $this->seededRole('owner');

        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($admin->id);

        $ownerUser = User::factory()->create();
        $ownerUser->roles()->attach($owner->id);

        $result = $this->action->execute();

        $this->assertSame(2, $result['stats']['total']);
        $this->assertSame(1, $result['stats']['owners']);
    }
}
