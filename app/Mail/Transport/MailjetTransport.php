<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

/**
 * Sends mail via Mailjet's HTTPS API (v3.1/send) rather than SMTP.
 *
 * This exists because Render blocks outbound SMTP on its free tier — every
 * SMTP-based provider (including Gmail) hangs for ~60s and then fails with a
 * connection timeout there. Mailjet's API is a plain HTTPS POST, which isn't
 * affected by that restriction.
 */
class MailjetTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $secretKey,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('MailjetTransport only supports Symfony Mime Email messages.');
        }

        $from = $email->getFrom()[0] ?? null;

        $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
            ->post('https://api.mailjet.com/v3.1/send', [
                'Messages' => [[
                    'From' => [
                        'Email' => $from?->getAddress(),
                        'Name' => $from?->getName() ?: null,
                    ],
                    'To' => array_map(
                        fn ($address) => [
                            'Email' => $address->getAddress(),
                            'Name' => $address->getName() ?: null,
                        ],
                        $email->getTo()
                    ),
                    'Subject' => $email->getSubject(),
                    'TextPart' => $email->getTextBody(),
                    'HTMLPart' => $email->getHtmlBody(),
                ]],
            ]);

        if ($response->failed()) {
            throw new TransportException('Mailjet API error ('.$response->status().'): '.$response->body());
        }
    }

    public function __toString(): string
    {
        return 'mailjet+api://api.mailjet.com';
    }
}
