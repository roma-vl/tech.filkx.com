<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRestoredNotification extends Notification
{
    use Queueable, ResolvesRecipientLocale;

    public function __construct(
        public readonly string $loginUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->recipientLocale($notifiable);

        return (new MailMessage)
            ->subject(__('emails.account_restored.subject', [], $locale))
            ->view('emails.auth.account_restored', [
                'userName' => $notifiable->name,
                'loginUrl' => $this->loginUrl,
                'locale' => $locale,
            ]);
    }
}
