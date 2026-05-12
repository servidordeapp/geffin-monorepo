<?php

namespace App\Modules\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GuardianResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(string $token)
    {
        parent::__construct($token);
        $this->connection = 'database';
        $this->queue = 'mails';
    }

    protected function resetUrl($notifiable): string
    {
        $base = config('app.guardian_frontend_url', 'http://localhost:3000');

        return $base.'/reset-password?token='.urlencode($this->token).'&email='.urlencode($notifiable->getEmailForPasswordReset());
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $this->resetUrl($notifiable))
            ->line('This link will expire in '.config('auth.passwords.guardians.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
