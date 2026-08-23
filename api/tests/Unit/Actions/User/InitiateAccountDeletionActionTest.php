<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\InitiateAccountDeletionAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitiateAccountDeletionActionTest extends TestCase
{
    use RefreshDatabase;

    private InitiateAccountDeletionAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(InitiateAccountDeletionAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_soft_deletes_the_user(): void
    {
        $user = $this->makeUser();

        $this->action->execute($user);

        $this->assertSoftDeleted($user);
    }
}
