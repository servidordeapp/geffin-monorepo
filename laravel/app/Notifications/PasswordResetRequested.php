<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PasswordResetRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [10, 60, 300];

    public function __construct(public string $token)
    {
    }

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            ['token' => $this->token, 'email' => $notifiable->email],
        );

        // TODO(bounce): add bounce/failure webhook handler to write email_sent:failed audit row
        return (new MailMessage())
            ->subject(__('auth.mail.reset.subject'))
            ->markdown('mail.auth.password-reset', [
                'url' => $url,
                'expiresInMinutes' => 60,
                'recipientName' => $notifiable->name,
            ]);
    }
}
