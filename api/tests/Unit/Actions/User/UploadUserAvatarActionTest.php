<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\UploadUserAvatarAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadUserAvatarActionTest extends TestCase
{
    use RefreshDatabase;

    private UploadUserAvatarAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UploadUserAvatarAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_stores_the_avatar_and_updates_the_users_avatar_path(): void
    {
        Storage::fake('public');
        $user = $this->makeUser(['avatar_path' => null]);
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $result = $this->action->execute($user, $avatar);

        Storage::disk('public')->assertExists($result->avatar_path);
        $this->assertStringStartsWith('avatars/', $result->avatar_path);
        $this->assertSame($result->avatar_path, $user->fresh()->avatar_path);
    }

    public function test_execute_replaces_the_previous_avatar_path_without_deleting_the_old_file(): void
    {
        Storage::fake('public');
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');
        $user = $this->makeUser(['avatar_path' => $oldPath]);
        $avatar = UploadedFile::fake()->image('new.jpg');

        $result = $this->action->execute($user, $avatar);

        $this->assertNotSame($oldPath, $result->avatar_path);
        Storage::disk('public')->assertExists($oldPath);
        Storage::disk('public')->assertExists($result->avatar_path);
    }
}
