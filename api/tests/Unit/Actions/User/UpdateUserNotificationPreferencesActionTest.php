<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\UpdateUserNotificationPreferencesAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserNotificationPreferencesActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserNotificationPreferencesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateUserNotificationPreferencesAction::class);
    }

    public function test_execute_saves_and_returns_the_new_preferences(): void
    {
        $user = User::factory()->create(['notification_preferences' => null]);
        $newPreferences = [
            'newsletter' => true,
            'productUpdates' => false,
            'marketingEmails' => true,
        ];

        $result = $this->action->execute($user, $newPreferences);

        $this->assertSame($newPreferences, $result);
        $this->assertSame($newPreferences, $user->fresh()->notification_preferences);
    }
}
