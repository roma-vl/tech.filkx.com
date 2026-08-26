<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    use ResolvesRecipientLocale;

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
        $locale = $this->recipientLocale($notifiable);

        return (new MailMessage)
            ->subject(__('emails.verify_email.subject', [], $locale))
            ->view('emails.auth.verify_email', [
                'userName' => $notifiable->name,
                'verificationUrl' => $verificationUrl,
                'locale' => $locale,
            ]);
    }
}
