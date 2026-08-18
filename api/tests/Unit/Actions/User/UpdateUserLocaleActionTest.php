<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\UpdateUserLocaleAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserLocaleActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserLocaleAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateUserLocaleAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_updates_the_users_locale(): void
    {
        $user = $this->makeUser(['locale' => 'uk']);

        $result = $this->action->execute($user, 'en');

        $this->assertSame('en', $result->locale);
        $this->assertSame('en', $user->fresh()->locale);
    }
}
