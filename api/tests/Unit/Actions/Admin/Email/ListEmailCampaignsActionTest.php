<?php

namespace Tests\Unit\Actions\Admin\Email;

use App\Api\Admin\Actions\Email\ListEmailCampaignsAction;
use Tests\TestCase;

class ListEmailCampaignsActionTest extends TestCase
{
    private ListEmailCampaignsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListEmailCampaignsAction::class);
    }

    public function test_execute_returns_the_seven_known_campaigns(): void
    {
        $result = $this->action->execute();

        $this->assertCount(7, $result);
        $this->assertSame(
            [
                'platform_update',
                'trial_recovery',
                'welcome_bonus',
                'onboarding_reminder',
                'subscription_expiring',
                'marketing_broadcast',
                'trial_activated',
            ],
            array_column($result, 'id')
        );
    }

    public function test_execute_returns_the_documented_shape_for_each_campaign(): void
    {
        $result = $this->action->execute();

        foreach ($result as $campaign) {
            $this->assertArrayHasKey('id', $campaign);
            $this->assertArrayHasKey('name', $campaign);
            $this->assertArrayHasKey('class', $campaign);
            $this->assertArrayHasKey('description', $campaign);
            $this->assertArrayHasKey('suggested_audience', $campaign);
            $this->assertIsString($campaign['class']);
        }
    }

    public function test_execute_maps_the_trial_recovery_campaign_to_the_trial_expired_audience(): void
    {
        $result = $this->action->execute();
        $trialRecovery = collect($result)->firstWhere('id', 'trial_recovery');

        $this->assertSame('trial_expired', $trialRecovery['suggested_audience']);
    }
}
