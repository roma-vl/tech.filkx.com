<?php

namespace App\Notifications;

use App\Traits\LocalizableEmail;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    use LocalizableEmail;

    /**
     * Build verification URL для SPA на окремому домені
     */
    protected function verificationUrl($notifiable): string
    {
        $params = [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
            'expires' => now()->addMinutes(60)->timestamp,
            'signature' => hash_hmac('sha256', $notifiable->getKey().$notifiable->getEmailForVerification(), config('app.key')),
        ];

        return config('app.frontend_url').'/verify-email?'.http_build_query($params);
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $view = $this->getLocalizedView('auth.verify_email', $notifiable->locale);
        $subject = $notifiable->locale === 'uk' ? 'Підтвердження електронної адреси' : 'Verify Email Address';

        return (new MailMessage)
            ->subject($subject)
            ->view($view, [
                'userName' => $notifiable->name,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
