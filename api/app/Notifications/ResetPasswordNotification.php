<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesRecipientLocale;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    use ResolvesRecipientLocale;

    /**
     * Build reset URL для SPA на окремому домені
     */
    protected function resetUrl($notifiable): string
    {
        $frontendUrl = config('app.frontend_url').'/reset-password';

        return $frontendUrl.'?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $locale = $this->recipientLocale($notifiable);

        return (new MailMessage)
            ->subject(__('emails.reset_password.subject', [], $locale))
            ->view('emails.auth.reset_password', [
                'resetUrl' => $url,
                'expire' => config('auth.passwords.users.expire'),
                'locale' => $locale,
            ]);
    }
}
