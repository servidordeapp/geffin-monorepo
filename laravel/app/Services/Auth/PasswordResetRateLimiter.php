<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\RateLimiter;

class PasswordResetRateLimiter
{
    public function hitOrFail(?string $email, string $ip): bool
    {
        if ($email !== null) {
            $emailKey = 'password-reset:email:'.sha1(mb_strtolower(trim($email)));

            if (RateLimiter::tooManyAttempts($emailKey, 5)) {
                return false;
            }

            RateLimiter::hit($emailKey, 3600);
        }

        $ipKey = 'password-reset:ip:'.$ip;

        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            return false;
        }

        RateLimiter::hit($ipKey, 3600);

        return true;
    }
}
