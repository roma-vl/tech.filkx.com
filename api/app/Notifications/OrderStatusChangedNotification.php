<?php

namespace App\Notifications;

use App\Api\V1\Enum\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = OrderStatusEnum::from($this->order->status)->label();

        return (new MailMessage)
            ->subject("Статус замовлення {$this->order->order_number}: {$statusLabel}")
            ->view('emails.orders.status_changed', [
                'order' => $this->order,
                'statusLabel' => $statusLabel,
                'accountUrl' => config('app.frontend_url').'/account',
            ]);
    }
}
