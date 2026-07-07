<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly int $ttlMinutes,
        string $locale,
    ) {
        // Setting this here (rather than relying on the ambient App::getLocale())
        // means the email is translated correctly even if this ever gets queued
        // and rendered outside of the original request's locale context.
        $this->locale($locale);
    }

    public function build(): self
    {
        return $this->subject(__('mail.two_factor.subject'))
            ->markdown('mail.two-factor-code', [
                'code' => $this->code,
                'ttlMinutes' => $this->ttlMinutes,
                'rtl' => $this->locale === 'he',
            ]);
    }
}
