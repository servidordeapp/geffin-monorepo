<?php

namespace App\Modules\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public string $connection = 'database';

    public string $queue = 'mails';

    protected function resetUrl($notifiable): string
    {
        $base = config('app.admin_frontend_url', 'http://localhost:3003');

        return $base.'/reset-password?token='.urlencode($this->token).'&email='.urlencode($notifiable->getEmailForPasswordReset());
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Admin Password')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $this->resetUrl($notifiable))
            ->line('This link will expire in '.config('auth.passwords.admins.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
