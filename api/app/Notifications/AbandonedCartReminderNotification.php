<?php

namespace App\Notifications;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Cart $cart,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? 'uk';

        $items = $this->cart->items->map(function (CartItem $item) use ($locale) {
            $name = $item->variant->product->name[$locale]
                ?? $item->variant->product->name['uk']
                ?? 'Товар';

            return [
                'name' => $name,
                'quantity' => $item->quantity,
                'price' => (float) $item->variant->price,
            ];
        });

        $total = $items->sum(fn (array $item) => $item['price'] * $item['quantity']);

        return (new MailMessage)
            ->subject('Ви залишили товари в кошику')
            ->view('emails.cart.abandoned', [
                'userName' => $notifiable->name,
                'items' => $items,
                'total' => $total,
                'cartUrl' => config('app.frontend_url', config('app.url')).'/cart',
            ]);
    }
}
