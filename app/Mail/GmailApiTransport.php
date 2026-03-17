<?php

namespace App\Mail;

use App\Services\GmailOAuthService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Log;

class GmailApiTransport extends AbstractTransport
{
    private GmailOAuthService $gmailService;

    public function __construct(GmailOAuthService $gmailService)
    {
        parent::__construct();
        $this->gmailService = $gmailService;
    }

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

    public function __toString(): string
    {
        return 'gmail-api';
    }
}
