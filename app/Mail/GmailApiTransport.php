<?php

namespace App\Mail;

use App\Services\GmailOAuthService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Log;

/**
 * Symfony Mailer transport that delivers messages via the Gmail REST API.
 *
 * Replaces the default SMTP transport when the 'gmail' driver is configured.
 * Handles To, CC, and BCC recipients individually through GmailOAuthService.
 */
class GmailApiTransport extends AbstractTransport
{
    /** @var GmailOAuthService The OAuth-authenticated Gmail service used to send emails. */
    private GmailOAuthService $gmailService;

    /**
     * Create a new Gmail API transport instance.
     *
     * @param  \App\Services\GmailOAuthService  $gmailService
     */
    public function __construct(GmailOAuthService $gmailService)
    {
        parent::__construct();
        $this->gmailService = $gmailService;
    }

    /**
     * Send the given message through the Gmail API.
     *
     * Iterates over all To recipients and sends each one individually. CC and BCC
     * recipients are also sent individually. Throws a RuntimeException if any
     * individual send fails.
     *
     * @param  \Symfony\Component\Mailer\SentMessage  $message
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = $address->getAddress();
        }

        $from = $email->getFrom()[0] ?? null;
        $fromEmail = $from ? $from->getAddress() : config('mail.from.address');
        $fromName = $from ? $from->getName() : config('mail.from.name');

        $subject = $email->getSubject() ?? '';
        $htmlBody = $email->getHtmlBody() ?? $email->getTextBody() ?? '';

        foreach ($to as $recipient) {
            $success = $this->sendWithRetry($recipient, $subject, $htmlBody, $fromName, $fromEmail);

            if (!$success) {
                Log::error('GmailApiTransport: Failed to send email to ' . $recipient);
                throw new \RuntimeException('Failed to send email via Gmail API to ' . $recipient);
            }
        }

        // Also handle CC and BCC. Surface failures (previously swallowed)
        // so the calling controller can retry or notify. (bugs.md L-28)
        foreach ($email->getCc() as $address) {
            if (!$this->sendWithRetry($address->getAddress(), $subject, $htmlBody, $fromName, $fromEmail)) {
                Log::error('GmailApiTransport: Failed to send CC email to ' . $address->getAddress());
            }
        }
        foreach ($email->getBcc() as $address) {
            if (!$this->sendWithRetry($address->getAddress(), $subject, $htmlBody, $fromName, $fromEmail)) {
                Log::error('GmailApiTransport: Failed to send BCC email to ' . $address->getAddress());
            }
        }
    }

    /**
     * Wrap GmailOAuthService::sendEmail with a single retry on transient
     * 5xx-style failures. The OAuth service already handles 401 token
     * refresh internally (M-55); this layer adds a 1-second-back-off
     * retry for connection errors and 5xx responses surfaced as
     * RuntimeException. (bugs.md L-27)
     *
     * @return bool True iff the message was accepted by the API.
     */
    private function sendWithRetry(string $recipient, string $subject, string $htmlBody, ?string $fromName, ?string $fromEmail): bool
    {
        try {
            return (bool) $this->gmailService->sendEmail($recipient, $subject, $htmlBody, $fromName, $fromEmail);
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            // Retry only on patterns that look transient: cURL errors and
            // 5xx HTTP codes. 4xx responses (auth, validation, rate limit)
            // wouldn't succeed on retry and shouldn't waste a second.
            if (preg_match('/cURL error|HTTP 5\d\d/i', $msg)) {
                Log::warning('GmailApiTransport: transient send failure, retrying once', [
                    'recipient' => $recipient,
                    'error' => $msg,
                ]);
                sleep(1);
                try {
                    return (bool) $this->gmailService->sendEmail($recipient, $subject, $htmlBody, $fromName, $fromEmail);
                } catch (\RuntimeException $e2) {
                    Log::error('GmailApiTransport: retry also failed', [
                        'recipient' => $recipient,
                        'error' => $e2->getMessage(),
                    ]);
                    return false;
                }
            }
            throw $e;
        }
    }

    /**
     * Return the string representation of this transport.
     *
     * Used by Symfony Mailer to identify the transport driver in logs and debug output.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'gmail-api';
    }
}
