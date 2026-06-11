<?php

namespace App\Api\Admin\Actions\Email;

use App\Mail\Campaigns\PlatformUpdateMail;
use App\Mail\Campaigns\TrialRecoveryMail;
use App\Mail\Campaigns\WelcomeBonusMail;
use App\Mail\MarketingMail;
use App\Mail\OnboardingReminderMail;
use App\Mail\SubscriptionExpiringSoonMail;
use App\Mail\TrialActivated;

class ListEmailCampaignsAction
{
    public function execute(): array
    {
        return [
            [
                'id' => 'platform_update',
                'name' => 'Monthly Platform Update',
                'class' => PlatformUpdateMail::class,
                'description' => 'Notify users about new features and improvements.',
                'suggested_audience' => 'all',
            ],
            [
                'id' => 'trial_recovery',
                'name' => 'Trial Recovery Offer',
                'class' => TrialRecoveryMail::class,
                'description' => 'Special discount for users with expired trials.',
                'suggested_audience' => 'trial_expired',
            ],
            [
                'id' => 'welcome_bonus',
                'name' => 'New User Welcome Bonus',
                'class' => WelcomeBonusMail::class,
                'description' => 'Engagement boost for users who recently signed up.',
                'suggested_audience' => 'new_users',
            ],
            [
                'id' => 'onboarding_reminder',
                'name' => 'Onboarding Reminder',
                'class' => OnboardingReminderMail::class,
                'description' => 'Gentle nudge for users who haven\'t finished setup.',
                'suggested_audience' => 'new_users',
            ],
            [
                'id' => 'subscription_expiring',
                'name' => 'Subscription Expiring Soon',
                'class' => SubscriptionExpiringSoonMail::class,
                'description' => 'Remind users to renew their subscription.',
                'suggested_audience' => 'active_subscribers',
            ],
            [
                'id' => 'marketing_broadcast',
                'name' => 'General Marketing Broadcast',
                'class' => MarketingMail::class,
                'description' => 'Generic template for special announcements.',
                'suggested_audience' => 'all',
            ],
            [
                'id' => 'trial_activated',
                'name' => 'Trial Activated Notification',
                'class' => TrialActivated::class,
                'description' => 'Welcome message with trial period details.',
                'suggested_audience' => 'new_users',
            ],
        ];
    }
}
