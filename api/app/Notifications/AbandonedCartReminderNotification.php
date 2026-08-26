<?php

namespace App\Notifications;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartReminderNotification extends Notification implements ShouldQueue
{
    use Queueable, ResolvesRecipientLocale;

    public function __construct(
        public readonly Cart $cart,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->recipientLocale($notifiable);

        $items = $this->cart->items->map(function (CartItem $item) use ($locale) {
            $name = $item->variant->product->name[$locale]
                ?? $item->variant->product->name[User::DEFAULT_LOCALE]
                ?? __('emails.abandoned_cart.item_fallback_name', [], $locale);

            return [
                'name' => $name,
                'quantity' => $item->quantity,
                'price' => (float) $item->variant->price,
            ];
        });

        $total = $items->sum(fn (array $item) => $item['price'] * $item['quantity']);

        return (new MailMessage)
            ->subject(__('emails.abandoned_cart.subject', [], $locale))
            ->view('emails.cart.abandoned', [
                'userName' => $notifiable->name,
                'items' => $items,
                'total' => $total,
                'cartUrl' => config('app.frontend_url', config('app.url')).'/cart',
                'locale' => $locale,
            ]);
    }
}
