<?php

namespace App\Api\V1\Enum;

use App\Models\User;

enum OrderStatusEnum: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Human-readable status label, translated for the given locale.
     *
     * Only consumed by OrderStatusChangedNotification today, hence the translation
     * strings living under the `emails.order_status.*` keys rather than a general-purpose
     * `order_status.*` namespace.
     */
    public function label(string $locale = User::DEFAULT_LOCALE): string
    {
        return __('emails.order_status.'.$this->value, [], $locale);
    }
}
