<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Notifications\GuardianEmailVerificationNotification;
use App\Modules\Auth\Requests\GuardianForgotPasswordRequest;
use App\Modules\Auth\Requests\GuardianLoginRequest;
use App\Modules\Auth\Requests\GuardianResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

class GuardianAuthController extends Controller
{
    public function login(GuardianLoginRequest $request): JsonResponse
    {
        $key = $request->input('email').'|'.$request->ip();

        if (RateLimiter::tooManyAttempts('login.guardian:'.$key, 5)) {
            return response()->json([
                'errors' => [['code' => 'TOO_MANY_ATTEMPTS', 'message' => 'Too many login attempts. Please try again later.']],
            ], 429);
        }

        $guardian = Auth::guard('guardian')->getProvider()->retrieveByCredentials([
            'email' => $request->email,
        ]);

        if (! $guardian || ! Hash::check($request->password, $guardian->password)) {
            RateLimiter::hit('login.guardian:'.$key, 15 * 60);

            return response()->json([
                'errors' => [['code' => 'INVALID_CREDENTIALS', 'message' => 'Invalid credentials.']],
            ], 401);
        }

        if (! $guardian->active) {
            return response()->json([
                'errors' => [['code' => 'ACCOUNT_INACTIVE', 'message' => 'Account is inactive.']],
            ], 403);
        }

        if (! $guardian->hasVerifiedEmail()) {
            return response()->json([
                'errors' => [['code' => 'EMAIL_NOT_VERIFIED', 'message' => 'Email address is not verified.']],
            ], 403);
        }

        RateLimiter::clear('login.guardian:'.$key);

        $token = $guardian->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $guardian->id,
                    'name' => $guardian->name,
                    'email' => $guardian->email,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('guardian')->currentAccessToken()->delete();

        return response()->json(['data' => ['message' => 'Logged out successfully.']]);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json([
                'errors' => [['code' => 'LINK_EXPIRED', 'message' => 'Verification link has expired.']],
            ], 400);
        }

        $guardian = Auth::guard('guardian')->getProvider()->retrieveById($id);

        if (! $guardian) {
            return response()->json([
                'errors' => [['code' => 'NOT_FOUND', 'message' => 'User not found.']],
            ], 404);
        }

        if (! hash_equals($hash, sha1($guardian->getEmailForVerification()))) {
            return response()->json([
                'errors' => [['code' => 'INVALID_LINK', 'message' => 'Invalid verification link.']],
            ], 400);
        }

        if ($guardian->hasVerifiedEmail()) {
            return response()->json([
                'errors' => [['code' => 'EMAIL_ALREADY_VERIFIED', 'message' => 'Email already verified.']],
            ], 400);
        }

        $guardian->markEmailAsVerified();

        return response()->json(['data' => ['message' => 'Email verified successfully.']]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|max:255']);

        if (RateLimiter::tooManyAttempts('resend.guardian:email:'.$request->email, 1)) {
            return response()->json([
                'errors' => [['code' => 'TOO_MANY_ATTEMPTS', 'message' => 'Please wait before requesting another verification email.']],
            ], 429);
        }

        RateLimiter::hit('resend.guardian:email:'.$request->email, 60);

        $guardian = Auth::guard('guardian')->getProvider()->retrieveByCredentials([
            'email' => $request->email,
        ]);

        if ($guardian && ! $guardian->hasVerifiedEmail()) {
            $guardian->notify(new GuardianEmailVerificationNotification());
        }

        return response()->json(['data' => ['message' => 'Verification email sent.']]);
    }

    public function forgotPassword(GuardianForgotPasswordRequest $request): JsonResponse
    {
        Password::broker('guardians')->sendResetLink($request->only('email'));

        return response()->json(['data' => ['message' => 'If that email address is in our system, we have sent a password reset link.']]);
    }

    public function resetPassword(GuardianResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('guardians')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($guardian, $password) {
                $guardian->forceFill(['password' => $password])->save();
                $guardian->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'errors' => [['code' => 'INVALID_RESET_TOKEN', 'message' => 'Invalid or expired reset token.']],
            ], 422);
        }

        return response()->json(['data' => ['message' => 'Password reset successfully.']]);
    }
}
