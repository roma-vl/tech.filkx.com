<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginNewDeviceNotification extends Notification
{
    use Queueable, ResolvesRecipientLocale;

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
        $locale = $this->recipientLocale($notifiable);

        return (new MailMessage)
            ->subject(__('emails.login_new_device.subject', [], $locale))
            ->view('emails.auth.login_new_device', [
                'userName' => $notifiable->name,
                'deviceName' => $this->deviceName,
                'location' => $this->location,
                'time' => $this->time,
                'settingsUrl' => config('app.frontend_url').'/account',
                'locale' => $locale,
            ]);
    }
}
