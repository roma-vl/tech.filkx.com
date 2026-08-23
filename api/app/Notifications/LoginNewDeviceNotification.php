<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginNewDeviceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $deviceName,
        public readonly string $location,
        public readonly string $time,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $notifiable->locale === 'uk' ? 'Новий вхід у ваш акаунт' : 'New login from a new device';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.auth.login_new_device', [
                'userName' => $notifiable->name,
                'deviceName' => $this->deviceName,
                'location' => $this->location,
                'time' => $this->time,
                'settingsUrl' => config('app.frontend_url').'/account',
            ]);
    }
}
