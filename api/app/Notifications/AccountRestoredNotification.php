<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRestoredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $loginUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $notifiable->locale === 'uk' ? 'Акаунт відновлено' : 'Account Restored';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.auth.account_restored', [
                'userName' => $notifiable->name,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}
