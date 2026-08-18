<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\RestoreDeletedAccountAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestoreDeletedAccountActionTest extends TestCase
{
    use RefreshDatabase;

    private RestoreDeletedAccountAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RestoreDeletedAccountAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_restores_a_soft_deleted_user(): void
    {
        $user = $this->makeUser();
        $user->delete();
        $this->assertSoftDeleted($user);

        $result = $this->action->execute($user->id);

        $this->assertNotSoftDeleted($user);
        $this->assertNull($user->fresh()->deleted_at);
        $this->assertSame($user->id, $result->id);
    }

    public function test_execute_throws_when_user_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
