<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\RevokeAllUserSessionsAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\AccessToken;
use Tests\TestCase;

class RevokeAllUserSessionsActionTest extends TestCase
{
    use RefreshDatabase;

    private RevokeAllUserSessionsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RevokeAllUserSessionsAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_revokes_all_active_tokens_when_there_is_no_current_token(): void
    {
        $user = $this->makeUser();
        $tokenOne = $user->createToken('session-one')->token;
        $tokenTwo = $user->createToken('session-two')->token;

        $revokedCount = $this->action->execute($user);

        $this->assertSame(2, $revokedCount);
        $this->assertTrue($tokenOne->fresh()->revoked);
        $this->assertTrue($tokenTwo->fresh()->revoked);
    }

    public function test_execute_leaves_the_current_token_untouched_and_revokes_the_rest(): void
    {
        $user = $this->makeUser();
        $currentToken = $user->createToken('current-session')->token;
        $otherToken = $user->createToken('other-session')->token;
        // Real requests carry an AccessToken (JWT-derived) via TokenGuard, not the raw Token
        // Eloquent model, so it's what execute()'s $user->token() actually resolves to.
        $user->withAccessToken(new AccessToken(['oauth_access_token_id' => $currentToken->id]));

        $revokedCount = $this->action->execute($user);

        $this->assertSame(1, $revokedCount);
        $this->assertFalse($currentToken->fresh()->revoked);
        $this->assertTrue($otherToken->fresh()->revoked);
    }

    public function test_execute_does_not_revoke_already_revoked_tokens_again(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('session')->token;
        $token->forceFill(['revoked' => true])->save();

        $revokedCount = $this->action->execute($user);

        $this->assertSame(0, $revokedCount);
    }
}
