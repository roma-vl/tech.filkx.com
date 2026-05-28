<?php

namespace App\Api\Admin\Actions\Email;

class ListEmailCampaignsAction
{
    public function execute(): array
    {
        return [
            [
                'id' => 'platform_update',
                'name' => 'Monthly Platform Update',
                'class' => \App\Mail\Campaigns\PlatformUpdateMail::class,
                'description' => 'Notify users about new features and improvements.',
                'suggested_audience' => 'all',
            ],
            [
                'id' => 'trial_recovery',
                'name' => 'Trial Recovery Offer',
                'class' => \App\Mail\Campaigns\TrialRecoveryMail::class,
                'description' => 'Special discount for users with expired trials.',
                'suggested_audience' => 'trial_expired',
            ],
            [
                'id' => 'welcome_bonus',
                'name' => 'New User Welcome Bonus',
                'class' => \App\Mail\Campaigns\WelcomeBonusMail::class,
                'description' => 'Engagement boost for users who recently signed up.',
                'suggested_audience' => 'new_users',
            ],
            [
                'id' => 'onboarding_reminder',
                'name' => 'Onboarding Reminder',
                'class' => \App\Mail\OnboardingReminderMail::class,
                'description' => 'Gentle nudge for users who haven\'t finished setup.',
                'suggested_audience' => 'new_users',
            ],
            [
                'id' => 'subscription_expiring',
                'name' => 'Subscription Expiring Soon',
                'class' => \App\Mail\SubscriptionExpiringSoonMail::class,
                'description' => 'Remind users to renew their subscription.',
                'suggested_audience' => 'active_subscribers',
            ],
            [
                'id' => 'marketing_broadcast',
                'name' => 'General Marketing Broadcast',
                'class' => \App\Mail\MarketingMail::class,
                'description' => 'Generic template for special announcements.',
                'suggested_audience' => 'all',
            ],
            [
                'id' => 'trial_activated',
                'name' => 'Trial Activated Notification',
                'class' => \App\Mail\TrialActivated::class,
                'description' => 'Welcome message with trial period details.',
                'suggested_audience' => 'new_users',
            ],
        ];
    }
}
