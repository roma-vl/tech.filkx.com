<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
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

        $subject = $notifiable->locale === 'uk' ? 'Підтвердження електронної адреси' : 'Verify Email Address';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.auth.verify_email', [
                'userName' => $notifiable->name,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
