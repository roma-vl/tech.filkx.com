<?php

namespace Tests\Unit\Actions\User;

use App\Api\V1\Actions\User\GetUserNotificationPreferencesAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserNotificationPreferencesActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserNotificationPreferencesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserNotificationPreferencesAction::class);
    }

    public function test_execute_returns_default_preferences_when_none_are_set(): void
    {
        $user = User::factory()->create(['notification_preferences' => null]);

        $result = $this->action->execute($user);

        $this->assertSame([
            'newsletter' => false,
            'product_updates' => true,
            'marketing_emails' => false,
        ], $result);
    }

    public function test_execute_returns_the_users_stored_preferences(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'newsletter' => true,
                'product_updates' => false,
                'marketing_emails' => true,
            ],
        ]);

        $result = $this->action->execute($user);

        $this->assertSame([
            'newsletter' => true,
            'product_updates' => false,
            'marketing_emails' => true,
        ], $result);
    }
}
