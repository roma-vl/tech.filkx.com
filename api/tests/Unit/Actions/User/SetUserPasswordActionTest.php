<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\SetUserPasswordAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetUserPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private SetUserPasswordAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(SetUserPasswordAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_sets_and_hashes_the_new_password(): void
    {
        $user = $this->makeUser();

        $result = $this->action->execute($user, 'new-secret-password');

        $this->assertTrue($result->is($user));
        $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    }
}
