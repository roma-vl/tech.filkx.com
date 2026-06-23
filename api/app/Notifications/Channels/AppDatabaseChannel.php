<?php

namespace App\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Notifications\Notification as BaseNotification;

class AppDatabaseChannel
{
    public function send(object $notifiable, BaseNotification $notification): void
    {
        $data = method_exists($notification, 'toDatabase')
            ? $notification->toDatabase($notifiable)
            : $notification->toArray($notifiable);

        Notification::create([
            'user_id' => $notifiable->id,
            'title'   => $data['title']   ?? 'Сповіщення',
            'content' => $data['content'] ?? '',
            'type'    => $data['type']    ?? 'system',
            'link'    => $data['link']    ?? null,
        ]);
    }
}
