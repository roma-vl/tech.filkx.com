<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\DeleteUserAvatarAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteUserAvatarActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteUserAvatarAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteUserAvatarAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_deletes_the_stored_avatar_and_clears_the_path(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('avatar.jpg')->store('avatars', 'public');
        $user = $this->makeUser(['avatar_path' => $path]);

        $result = $this->action->execute($user);

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($result->avatar_path);
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_execute_does_nothing_when_user_has_no_avatar(): void
    {
        Storage::fake('public');
        $user = $this->makeUser(['avatar_path' => null]);

        $result = $this->action->execute($user);

        $this->assertNull($result->avatar_path);
    }
}
