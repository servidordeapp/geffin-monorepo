<?php

declare(strict_types=1);

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'forgot' => [
        'title' => 'Forgot your password',
        'subtitle' => 'Enter your email and we will send you a link to create a new password.',
        'submit' => 'Send reset link',
        'back_to_login' => 'Back to login',
    ],

    'reset' => [
        'title' => 'Create new password',
        'subtitle' => 'Choose a strong password with at least 12 characters.',
        'password_label' => 'New password',
        'password_confirmation_label' => 'Confirm new password',
        'submit' => 'Update password',
        'request_new_link' => 'Request new link',
    ],

    'mail' => [
        'reset' => [
            'subject' => 'Reset your Geffin password',
        ],
    ],
];
