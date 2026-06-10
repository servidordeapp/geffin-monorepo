<?php

declare(strict_types=1);

namespace App\Enums\Auth;

enum PasswordResetEventTypeEnum: string
{
    case Requested = 'requested';
    case EmailSent = 'email_sent';
    case LinkOpened = 'link_opened';
    case PasswordChanged = 'password_changed';
    case TokenRejected = 'token_rejected';
    case RequestThrottled = 'request_throttled';
}
