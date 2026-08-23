<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\UpdateUserProfileAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserProfileActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserProfileAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateUserProfileAction::class);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    public function test_execute_updates_name_and_email(): void
    {
        $user = $this->makeUser(['name' => 'Old Name', 'email' => 'old@example.com']);

        $result = $this->action->execute($user, ['name' => 'New Name', 'email' => 'new@example.com']);

        $this->assertSame('New Name', $result->name);
        $this->assertSame('new@example.com', $result->email);
        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_execute_keeps_existing_name_and_email_when_not_provided(): void
    {
        $user = $this->makeUser(['name' => 'Old Name', 'email' => 'old@example.com']);

        $result = $this->action->execute($user, []);

        $this->assertSame('Old Name', $result->name);
        $this->assertSame('old@example.com', $result->email);
    }

    public function test_execute_stores_phone_language_and_addresses_inside_settings(): void
    {
        $user = $this->makeUser(['settings' => null]);

        $result = $this->action->execute($user, [
            'phone' => '+380000000000',
            'language' => 'uk',
            'addresses' => [['city' => 'Kyiv']],
        ]);

        $this->assertSame('+380000000000', $result->settings['phone']);
        $this->assertSame('uk', $result->settings['language']);
        $this->assertSame([['city' => 'Kyiv']], $result->settings['addresses']);

        $fresh = $user->fresh();
        $this->assertSame('+380000000000', $fresh->settings['phone']);
    }

    public function test_execute_merges_into_existing_settings_without_dropping_untouched_keys(): void
    {
        $user = $this->makeUser(['settings' => ['phone' => '+111', 'language' => 'en']]);

        $result = $this->action->execute($user, ['language' => 'uk']);

        $this->assertSame('+111', $result->settings['phone']);
        $this->assertSame('uk', $result->settings['language']);
    }

    public function test_execute_leaves_settings_keys_untouched_when_keys_are_absent_from_data(): void
    {
        $user = $this->makeUser(['settings' => ['phone' => '+111']]);

        $result = $this->action->execute($user, ['name' => 'New Name']);

        $this->assertSame('+111', $result->settings['phone']);
    }
}
