<?php

namespace App\Notifications\Concerns;

use App\Models\User;

/**
 * Every transactional email must render in the recipient's locale, both subject and
 * body. This is the single place that decides which locale that is, so every
 * Notification resolves it the same way instead of re-deriving it (or hardcoding a
 * language) independently.
 */
trait ResolvesRecipientLocale
{
    /**
     * Resolve the locale to render this notification in for a given notifiable.
     *
     * Works for real `App\Models\User` notifiables (which carry a `locale` column) as well
     * as notifiables that don't have one at all (e.g. `Notification::route('mail', $email)`
     * for guest checkout) - those fall back to the app-wide default.
     */
    protected function recipientLocale(object $notifiable): string
    {
        return $this->localeOrDefault($notifiable->locale ?? null);
    }

    /**
     * Same fallback, for notifications that don't have a direct notifiable to read a
     * locale from (e.g. an order confirmation routed to a guest's email address) and
     * instead resolve it from a related model, such as the order's owning user.
     */
    protected function localeOrDefault(?string $locale): string
    {
        return $locale ?: User::DEFAULT_LOCALE;
    }
}
