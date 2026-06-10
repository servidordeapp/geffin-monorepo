<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Get the reset password notification mail message for the given URL.
     */
    protected function buildMailMessage($url): MailMessage
    {
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Redefinição de senha — '.config('app.name'))
            ->greeting('Olá!')
            ->line('Recebemos uma solicitação de redefinição de senha para a sua conta.')
            ->action('Redefinir senha', $url)
            ->line("Este link expira em {$expireMinutes} minutos.")
            ->line('Se você não solicitou a redefinição, nenhuma ação é necessária.')
            ->salutation('Equipe '.config('app.name'));
    }
}
