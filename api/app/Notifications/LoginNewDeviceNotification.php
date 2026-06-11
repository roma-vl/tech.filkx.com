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
        return (new MailMessage)
            ->subject('New login from a new device')
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line('We detected a new login to your account from an unrecognised device.')
            ->line('**Device:** '.$this->deviceName)
            ->line('**Location / IP:** '.$this->location)
            ->line('**Time:** '.$this->time)
            ->line('If this was you, you can safely ignore this email.')
            ->line('If you did not log in, please change your password immediately.')
            ->action('Change Password', url('/auth/password/reset'))
            ->salutation('The '.config('app.name').' Team');
    }
}
