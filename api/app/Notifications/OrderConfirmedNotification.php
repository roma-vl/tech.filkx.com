<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification
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

        return (new MailMessage)
            ->subject(__('emails.order_confirmed.subject', ['number' => $this->order->order_number], $locale))
            ->view('emails.orders.confirmed', [
                'order' => $this->order,
                'accountUrl' => config('app.frontend_url').'/account',
                'locale' => $locale,
            ]);
    }
}
