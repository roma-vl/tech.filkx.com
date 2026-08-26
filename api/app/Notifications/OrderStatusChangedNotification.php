<?php

namespace App\Notifications;

use App\Api\V1\Enum\OrderStatusEnum;
use App\Models\Order;
use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable, ResolvesRecipientLocale;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Guest checkout routes this via Notification::route('mail', $order->customer_email),
        // an anonymous notifiable with no locale of its own - resolve it from the order's
        // owning user (registered checkout) instead, falling back to the default otherwise.
        $locale = $this->localeOrDefault($this->order->user?->locale);
        $statusLabel = OrderStatusEnum::from($this->order->status)->label($locale);

        return (new MailMessage)
            ->subject(__('emails.order_status_changed.subject', [
                'number' => $this->order->order_number,
                'status' => $statusLabel,
            ], $locale))
            ->view('emails.orders.status_changed', [
                'order' => $this->order,
                'statusLabel' => $statusLabel,
                'accountUrl' => config('app.frontend_url').'/account',
                'locale' => $locale,
            ]);
    }
}
