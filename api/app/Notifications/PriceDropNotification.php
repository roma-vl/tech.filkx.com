<?php

namespace App\Notifications;

use App\Models\Product;
use App\Notifications\Channels\AppDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceDropNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly float   $oldPrice,
        public readonly float   $newPrice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', AppDatabaseChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName = is_array($this->product->name)
            ? ($this->product->name[$notifiable->locale ?? 'uk'] ?? $this->product->name['uk'] ?? '')
            : $this->product->name;

        $dropPercent = round((($this->oldPrice - $this->newPrice) / $this->oldPrice) * 100, 1);
        $saving      = round($this->oldPrice - $this->newPrice, 2);
        $productUrl  = config('app.frontend_url', config('app.url')) . '/products/' . $this->product->slug;

        return (new MailMessage)
            ->subject("↓ Знижка {$dropPercent}% на «{$productName}»")
            ->view('emails.price-drop', [
                'userName'    => $notifiable->name,
                'productName' => $productName,
                'oldPrice'    => $this->oldPrice,
                'newPrice'    => $this->newPrice,
                'saving'      => $saving,
                'dropPercent' => $dropPercent,
                'productUrl'  => $productUrl,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $productName = is_array($this->product->name)
            ? ($this->product->name[$notifiable->locale ?? 'uk'] ?? $this->product->name['uk'] ?? '')
            : $this->product->name;

        $dropPercent = round((($this->oldPrice - $this->newPrice) / $this->oldPrice) * 100, 1);

        return [
            'type'    => 'price_drop',
            'title'   => "Знижка {$dropPercent}% на «{$productName}»",
            'content' => "Ціна знизилась з {$this->oldPrice} до {$this->newPrice} грн",
            'link'    => '/products/' . $this->product->slug,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
