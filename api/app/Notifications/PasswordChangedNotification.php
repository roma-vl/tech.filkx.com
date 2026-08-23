<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable;

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
        $subject = $notifiable->locale === 'uk' ? 'Пароль змінено' : 'Password Changed';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.auth.password_changed', [
                'userName' => $notifiable->name,
                'time' => $this->time,
                'ipAddress' => $this->ipAddress,
                'settingsUrl' => config('app.frontend_url').'/account',
            ]);
    }
}
