<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
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

        $subject = $notifiable->locale === 'uk' ? 'Відновлення пароля' : 'Reset Password Notification';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.auth.reset_password', [
                'resetUrl' => $url,
                'expire' => config('auth.passwords.users.expire'),
            ]);
    }
}
