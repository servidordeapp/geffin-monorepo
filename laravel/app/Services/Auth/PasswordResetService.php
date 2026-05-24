<?php

namespace App\Services\Auth;

use App\Enums\Auth\PasswordResetEventTypeEnum;
use App\Models\PasswordResetAuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function __construct(
        public PasswordResetRateLimiter $rateLimiter
    ) {}

    public function request(string $email, Request $request): void
    {
        $user = $this->resolveAccount($email);

        PasswordResetAuditEvent::create([
            'event_type' => PasswordResetEventTypeEnum::Requested,
            'user_id' => $user?->id,
            'email_hash' => PasswordResetAuditEvent::emailHash($email),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'outcome' => 'accepted',
            'created_at' => now(),
        ]);

        if ($user !== null) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
        }

        // Always call the broker — its built-in Timebox (200 ms) equalises known vs unknown timing.
        // Unknown emails return INVALID_USER inside the timebox; no token or notification is created.
        // TODO(i18n): propagate per-user locale once users.preferred_locale column exists
        Password::broker()->sendResetLink(['email' => $email]);
    }

    public function recordLinkOpened(string $email, Request $request): void
    {
        $user = $this->resolveAccount($email);

        PasswordResetAuditEvent::create([
            'event_type' => PasswordResetEventTypeEnum::LinkOpened,
            'user_id' => $user?->id,
            'email_hash' => PasswordResetAuditEvent::emailHash($email),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'outcome' => 'accepted',
            'created_at' => now(),
        ]);
    }

    public function reset(string $email, string $token, string $password, Request $request): string
    {
        $status = Password::PASSWORD_RESET;

        DB::transaction(function () use ($email, $token, $password, $request, &$status) {
            $status = Password::broker()->reset(
                [
                    'email' => $email,
                    'password' => $password,
                    'password_confirmation' => $password,
                    'token' => $token,
                ],
                function (User $user, string $plain) use ($request) {
                    $user->forceFill(['password' => Hash::make($plain)])->save();
                    $user->forceFill(['remember_token' => Str::random(60)])->save();
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                    // TODO(sanctum): revoke personal access tokens once Sanctum is configured

                    PasswordResetAuditEvent::create([
                        'event_type' => PasswordResetEventTypeEnum::PasswordChanged,
                        'user_id' => $user->id,
                        'email_hash' => PasswordResetAuditEvent::emailHash($user->email),
                        'ip_address' => $request->ip(),
                        'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                        'outcome' => 'accepted',
                        'created_at' => now(),
                    ]);
                }
            );
        });

        if ($status !== Password::PASSWORD_RESET) {
            $eventType = $status === Password::RESET_THROTTLED ? PasswordResetEventTypeEnum::RequestThrottled : PasswordResetEventTypeEnum::TokenRejected;
            $outcome = $status === Password::RESET_THROTTLED ? 'throttled' : 'rejected';

            PasswordResetAuditEvent::create([
                'event_type' => $eventType,
                'user_id' => null,
                'email_hash' => PasswordResetAuditEvent::emailHash($email),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'outcome' => $outcome,
                'reason' => $status === Password::RESET_THROTTLED ? null : 'invalid',
                'created_at' => now(),
            ]);
        }

        /** @var string $status */
        return $status;
    }

    private function resolveAccount(string $email): ?User
    {
        // TODO(status): filter by active status once users.status column is added
        return User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])->first();
    }
}
