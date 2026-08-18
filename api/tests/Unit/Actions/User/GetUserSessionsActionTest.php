<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\GetUserSessionsAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserSessionsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserSessionsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserSessionsAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_returns_only_active_tokens_as_arrays(): void
    {
        $user = $this->makeUser();
        $active = $user->createToken('active-session')->token;
        $revoked = $user->createToken('revoked-session')->token;
        $revoked->forceFill(['revoked' => true])->save();

        $sessions = $this->action->execute($user);

        $this->assertCount(1, $sessions);
        $this->assertSame($active->id, $sessions[0]['id']);
        $this->assertArrayHasKey('client_id', $sessions[0]);
        $this->assertArrayHasKey('name', $sessions[0]);
        $this->assertArrayHasKey('expires_at', $sessions[0]);
        $this->assertArrayHasKey('created_at', $sessions[0]);
        $this->assertSame('active-session', $sessions[0]['name']);
    }

    public function test_execute_returns_an_empty_array_when_user_has_no_tokens(): void
    {
        $user = $this->makeUser();

        $sessions = $this->action->execute($user);

        $this->assertSame([], $sessions);
    }
}
