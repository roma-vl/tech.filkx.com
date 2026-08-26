<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use App\Notifications\Channels\AppDatabaseChannel;
use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceDropNotification extends Notification
{
    use Queueable, ResolvesRecipientLocale;

    public function __construct(
        public readonly Product $product,
        public readonly float $oldPrice,
        public readonly float $newPrice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', AppDatabaseChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->recipientLocale($notifiable);
        $productName = $this->productName($locale);
        $dropPercent = $this->dropPercent();
        $saving = round($this->oldPrice - $this->newPrice, 2);
        $productUrl = config('app.frontend_url', config('app.url')).'/product/'.$this->product->slug;

        return (new MailMessage)
            ->subject(__('emails.price_drop.subject', ['percent' => $dropPercent, 'product' => $productName], $locale))
            ->view('emails.price-drop', [
                'userName' => $notifiable->name,
                'productName' => $productName,
                'oldPrice' => $this->oldPrice,
                'newPrice' => $this->newPrice,
                'saving' => $saving,
                'dropPercent' => $dropPercent,
                'productUrl' => $productUrl,
                'locale' => $locale,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $locale = $this->recipientLocale($notifiable);
        $productName = $this->productName($locale);
        $dropPercent = $this->dropPercent();

        return [
            'type' => 'price_drop',
            'title' => __('emails.price_drop.db_title', ['percent' => $dropPercent, 'product' => $productName], $locale),
            'content' => __('emails.price_drop.db_content', [
                'old' => $this->oldPrice,
                'new' => $this->newPrice,
            ], $locale),
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

    private function dropPercent(): float
    {
        return round((($this->oldPrice - $this->newPrice) / $this->oldPrice) * 100, 1);
    }
}
