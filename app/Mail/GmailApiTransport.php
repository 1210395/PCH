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
            $success = $this->gmailService->sendEmail(
                $recipient,
                $subject,
                $htmlBody,
                $fromName,
                $fromEmail
            );

            if (!$success) {
                Log::error('GmailApiTransport: Failed to send email to ' . $recipient);
                throw new \RuntimeException('Failed to send email via Gmail API to ' . $recipient);
            }
        }

        // Also handle CC and BCC
        foreach ($email->getCc() as $address) {
            $this->gmailService->sendEmail($address->getAddress(), $subject, $htmlBody, $fromName, $fromEmail);
        }
        foreach ($email->getBcc() as $address) {
            $this->gmailService->sendEmail($address->getAddress(), $subject, $htmlBody, $fromName, $fromEmail);
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
