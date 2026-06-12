<?php

declare(strict_types=1);

namespace App\Notifications\Tenant;

use App\Notifications\ResetPasswordNotification;

class TenantResetPasswordNotification extends ResetPasswordNotification
{
    /**
     * Build the password reset URL for the tenant domain.
     *
     * The notification is sent while tenancy is initialized, so the current
     * request host is the tenant's own domain. Pointing at the tenant-scoped
     * route keeps the reset link inside the tenant context.
     */
    protected function resetUrl($notifiable): string
    {
        return route('tenant.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
