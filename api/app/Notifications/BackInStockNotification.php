<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use App\Notifications\Channels\AppDatabaseChannel;
use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackInStockNotification extends Notification
{
    use Queueable, ResolvesRecipientLocale;

    public function __construct(
        public readonly Product $product,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', AppDatabaseChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->recipientLocale($notifiable);
        $productName = $this->productName($locale);
        $productUrl = config('app.frontend_url', config('app.url')).'/product/'.$this->product->slug;

        return (new MailMessage)
            ->subject(__('emails.back_in_stock.subject', ['product' => $productName], $locale))
            ->view('emails.back-in-stock', [
                'userName' => $notifiable->name,
                'productName' => $productName,
                'productUrl' => $productUrl,
                'locale' => $locale,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $locale = $this->recipientLocale($notifiable);
        $productName = $this->productName($locale);

        return [
            'type' => 'back_in_stock',
            'title' => __('emails.back_in_stock.db_title', ['product' => $productName], $locale),
            'content' => __('emails.back_in_stock.db_content', [], $locale),
            'link' => '/product/'.$this->product->slug,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    private function productName(string $locale): string
    {
        return is_array($this->product->name)
            ? ($this->product->name[$locale] ?? $this->product->name[User::DEFAULT_LOCALE] ?? '')
            : $this->product->name;
    }
}
