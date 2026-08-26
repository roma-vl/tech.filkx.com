<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable, ResolvesRecipientLocale;

    public function __construct(
        public readonly string $ipAddress,
        public readonly string $time,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->recipientLocale($notifiable);

        return (new MailMessage)
            ->subject(__('emails.password_changed.subject', [], $locale))
            ->view('emails.auth.password_changed', [
                'userName' => $notifiable->name,
                'time' => $this->time,
                'ipAddress' => $this->ipAddress,
                'settingsUrl' => config('app.frontend_url').'/account',
                'locale' => $locale,
            ]);
    }
}
