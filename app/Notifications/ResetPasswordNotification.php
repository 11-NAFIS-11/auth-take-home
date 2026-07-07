<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(
        public readonly string $token,
        string $locale,
    ) {
        $this->locale($locale);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('mail.password_reset.subject'))
            ->markdown('mail.reset-password', [
                'url' => $url,
                'rtl' => $this->locale === 'he',
            ]);
    }
}
