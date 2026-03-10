<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();
        $verificationUrl = $this->verificationUrl($notifiable);

        $subject = $locale === 'ar'
            ? 'تأكيد البريد الإلكتروني - ' . config('app.name')
            : 'Verify Your Email - ' . config('app.name');

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.verify-email', [
                'verificationUrl' => $verificationUrl,
                'name' => $notifiable->first_name ?? $notifiable->name,
                'locale' => $locale,
            ]);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl(object $notifiable): string
    {
        $locale = app()->getLocale();

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(24),
            [
                'locale' => $locale,
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
