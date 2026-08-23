<?php

namespace Tests\Unit\Actions\Admin\Email;

use App\Api\Admin\Actions\Email\SendEmailCampaignAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * NOTE: App\Jobs\SendCampaignMailJob (dispatched by the action) does not exist anywhere in the
 * codebase, and the "trial_expired"/"active_subscribers" audiences query a "subscription"
 * relation that User does not define (the subscription module is not implemented - see
 * UserResource::toArray()). This action is also not wired to any controller/route, so it is
 * unreachable dead code today. These are pre-existing bugs, not something introduced here;
 * the tests below cover the paths that do work and pin down the current (broken) behaviour of
 * the rest so a future fix has a regression test to satisfy.
 */
class SendEmailCampaignActionTest extends TestCase
{
    use RefreshDatabase;

    private SendEmailCampaignAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(SendEmailCampaignAction::class);
    }

    public function test_execute_returns_zero_when_no_users_match_the_new_users_audience(): void
    {
        User::factory()->create(['created_at' => now()->subDays(30)]);

        $result = $this->action->execute('SomeCampaign', 'new_users');

        $this->assertSame(0, $result);
    }

    public function test_execute_returns_zero_for_an_unrecognised_audience_when_no_users_exist(): void
    {
        $result = $this->action->execute('SomeCampaign', 'unknown_audience');

        $this->assertSame(0, $result);
    }

    public function test_execute_throws_when_a_matching_user_triggers_the_missing_campaign_mail_job(): void
    {
        User::factory()->create(['created_at' => now()]);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Class "App\Jobs\SendCampaignMailJob" not found');

        $this->action->execute('SomeCampaign', 'new_users');
    }

    public function test_execute_throws_for_the_trial_expired_audience_because_user_has_no_subscription_relation(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method App\Models\User::subscription()');

        $this->action->execute('SomeCampaign', 'trial_expired');
    }

    public function test_execute_throws_for_the_active_subscribers_audience_because_user_has_no_subscription_relation(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method App\Models\User::subscription()');

        $this->action->execute('SomeCampaign', 'active_subscribers');
    }
}
