<?php

namespace App\Traits;

trait LocalizableEmail
{
    /**
     * Get the localized view path.
     *
     * @param  string  $baseView  View name without 'emails.' prefix (e.g. 'auth.verify_email')
     */
    public function getLocalizedView(string $baseView, ?string $locale = null): string
    {
        $locale = $locale ?: config('app.locale', 'en');

        // Ensure we support fallback if a language doesn't have the template yet
        $view = "emails.{$locale}.{$baseView}";

        if (! view()->exists($view)) {
            $view = "emails.en.{$baseView}";
        }

        return $view;
    }
}
