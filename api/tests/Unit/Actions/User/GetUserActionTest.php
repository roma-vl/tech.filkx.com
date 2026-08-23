<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\GetUserAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class GetUserActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_returns_the_currently_authenticated_api_user(): void
    {
        $user = $this->makeUser();
        Auth::guard('api')->setUser($user);

        $result = $this->action->execute();

        $this->assertTrue($result->is($user));
    }

    public function test_execute_throws_a_type_error_when_no_user_is_authenticated(): void
    {
        // execute()'s declared return type is the non-nullable User, but Auth::guard('api')->user()
        // returns null when unauthenticated, so PHP's return-type enforcement throws here.
        $this->expectException(\TypeError::class);

        $this->action->execute();
    }
}
