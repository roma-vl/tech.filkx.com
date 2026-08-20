<?php

namespace App\Traits;

trait LocalizableEmail
{
    /**
     * Get the view path for an email template.
     *
     * @param  string  $baseView  View name without 'emails.' prefix (e.g. 'auth.verify_email')
     */
    public function getLocalizedView(string $baseView, ?string $locale = null): string
    {
        return "emails.{$baseView}";
    }
}
