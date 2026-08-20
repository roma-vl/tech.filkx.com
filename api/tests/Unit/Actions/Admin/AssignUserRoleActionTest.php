<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\AssignUserRoleAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignUserRoleActionTest extends TestCase
{
    use RefreshDatabase;

    private AssignUserRoleAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(AssignUserRoleAction::class);
    }

    private function makeRole(string $slug): Role
    {
        return Role::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'scope' => 'global',
        ]);
    }

    public function test_execute_assigns_the_given_roles_to_the_user(): void
    {
        $user = User::factory()->create();
        $editor = $this->makeRole('editor');
        $viewer = $this->makeRole('viewer');

        $this->action->execute($user, ['editor', 'viewer']);

        $slugs = $user->roles()->pluck('slug')->all();
        $this->assertEqualsCanonicalizing(['editor', 'viewer'], $slugs);
    }

    public function test_execute_replaces_previously_assigned_roles_not_in_the_new_list(): void
    {
        $user = User::factory()->create();
        $editor = $this->makeRole('editor');
        $viewer = $this->makeRole('viewer');
        $user->roles()->attach($editor->id);

        $this->action->execute($user, ['viewer']);

        $slugs = $user->roles()->pluck('slug')->all();
        $this->assertSame(['viewer'], $slugs);
    }

    public function test_execute_ignores_slugs_that_do_not_match_any_role(): void
    {
        $user = User::factory()->create();
        $this->makeRole('editor');

        $this->action->execute($user, ['editor', 'does-not-exist']);

        $slugs = $user->roles()->pluck('slug')->all();
        $this->assertSame(['editor'], $slugs);
    }

    public function test_execute_with_an_empty_list_clears_all_roles(): void
    {
        $user = User::factory()->create();
        $editor = $this->makeRole('editor');
        $user->roles()->attach($editor->id);

        $this->action->execute($user, []);

        $this->assertSame(0, $user->roles()->count());
    }

    public function test_execute_forgets_the_users_cached_permissions(): void
    {
        $user = User::factory()->create();
        $this->makeRole('editor');
        cache()->put("user.{$user->id}.permissions", collect(['stale']), 3600);

        $this->action->execute($user, ['editor']);

        $this->assertFalse(cache()->has("user.{$user->id}.permissions"));
    }
}
