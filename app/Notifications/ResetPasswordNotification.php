<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

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
        $resetUrl = url("/{$locale}/password/reset/{$this->token}") . '?' . http_build_query(['email' => $notifiable->getEmailForPasswordReset()]);

        $subject = $locale === 'ar'
            ? 'إعادة تعيين كلمة المرور - ' . config('app.name')
            : 'Reset Your Password - ' . config('app.name');

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.reset-password', [
                'resetUrl' => $resetUrl,
                'name' => $notifiable->first_name ?? $notifiable->name,
                'locale' => $locale,
            ]);
    }
}
