<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeletionScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $restoreUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $notifiable->locale === 'uk' ? 'Акаунт заплановано до видалення' : 'Account Scheduled for Deletion';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.auth.account_deletion_scheduled', [
                'userName' => $notifiable->name,
                'restoreUrl' => $this->restoreUrl,
            ]);
    }
}
