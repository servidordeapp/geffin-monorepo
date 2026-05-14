<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Notifications\AdminEmailVerificationNotification;
use App\Modules\Auth\Requests\AdminForgotPasswordRequest;
use App\Modules\Auth\Requests\AdminLoginRequest;
use App\Modules\Auth\Requests\AdminResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $key = $request->input('email').'|'.$request->ip();

        if (RateLimiter::tooManyAttempts('login.admin:'.$key, 5)) {
            return response()->json([
                'errors' => [['code' => 'TOO_MANY_ATTEMPTS', 'message' => 'Too many login attempts. Please try again later.']],
            ], 429);
        }

        $admin = Auth::guard('admin')->getProvider()->retrieveByCredentials([
            'email' => $request->email,
        ]);

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            RateLimiter::hit('login.admin:'.$key, 15 * 60);

            return response()->json([
                'errors' => [['code' => 'INVALID_CREDENTIALS', 'message' => 'Invalid credentials.']],
            ], 401);
        }

        if (! $admin->active) {
            return response()->json([
                'errors' => [['code' => 'ACCOUNT_INACTIVE', 'message' => 'Account is inactive.']],
            ], 403);
        }

        if (! $admin->hasVerifiedEmail()) {
            return response()->json([
                'errors' => [['code' => 'EMAIL_NOT_VERIFIED', 'message' => 'Email address is not verified.']],
            ], 403);
        }

        RateLimiter::clear('login.admin:'.$key);

        $token = $admin->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('admin')->currentAccessToken()->delete();

        return response()->json(['data' => ['message' => 'Logged out successfully.']]);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json([
                'errors' => [['code' => 'LINK_EXPIRED', 'message' => 'Verification link has expired.']],
            ], 400);
        }

        $admin = Auth::guard('admin')->getProvider()->retrieveById($id);

        if (! $admin) {
            return response()->json([
                'errors' => [['code' => 'NOT_FOUND', 'message' => 'User not found.']],
            ], 404);
        }

        if (! hash_equals($hash, sha1($admin->getEmailForVerification()))) {
            return response()->json([
                'errors' => [['code' => 'INVALID_LINK', 'message' => 'Invalid verification link.']],
            ], 400);
        }

        if ($admin->hasVerifiedEmail()) {
            return response()->json([
                'errors' => [['code' => 'EMAIL_ALREADY_VERIFIED', 'message' => 'Email already verified.']],
            ], 400);
        }

        $admin->markEmailAsVerified();

        return response()->json(['data' => ['message' => 'Email verified successfully.']]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|max:255']);

        if (RateLimiter::tooManyAttempts('resend.admin:email:'.$request->email, 1)) {
            return response()->json([
                'errors' => [['code' => 'TOO_MANY_ATTEMPTS', 'message' => 'Please wait before requesting another verification email.']],
            ], 429);
        }

        RateLimiter::hit('resend.admin:email:'.$request->email, 60);

        $admin = Auth::guard('admin')->getProvider()->retrieveByCredentials([
            'email' => $request->email,
        ]);

        if ($admin && ! $admin->hasVerifiedEmail()) {
            $admin->notify(new AdminEmailVerificationNotification());
        }

        return response()->json(['data' => ['message' => 'Verification email sent.']]);
    }

    public function forgotPassword(AdminForgotPasswordRequest $request): JsonResponse
    {
        Password::broker('admins')->sendResetLink($request->only('email'));

        return response()->json(['data' => ['message' => 'If that email address is in our system, we have sent a password reset link.']]);
    }

    public function resetPassword(AdminResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                $admin->forceFill(['password' => $password])->save();
                $admin->tokens()->delete();
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
