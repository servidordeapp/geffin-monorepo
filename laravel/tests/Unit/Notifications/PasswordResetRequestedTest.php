<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\PasswordResetRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;

uses(RefreshDatabase::class);

it('implements ShouldQueue with the expected retry policy', function () {
    $notification = new PasswordResetRequested('token-abc');

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($notification->tries)->toBe(3)
        ->and($notification->backoff)->toBe([10, 60, 300]);
});

it('stores the token passed to the constructor', function () {
    $notification = new PasswordResetRequested('my-token');

    expect($notification->token)->toBe('my-token');
});

it('routes through the mail channel only', function () {
    $notification = new PasswordResetRequested('token-abc');

    expect($notification->via(new stdClass()))->toBe(['mail']);
});

it('builds a mail message with the localized subject', function () {
    $user = User::factory()->create();
    $notification = new PasswordResetRequested('token-xyz');

    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('auth.mail.reset.subject'))
        ->and($mail->markdown)->toBe('mail.auth.password-reset');
});

it('renders a signed reset URL that expires in 60 minutes', function () {
    $user = User::factory()->create(['email' => 'alvo@example.test']);
    $notification = new PasswordResetRequested('signed-token');

    $mail = $notification->toMail($user);

    $url = $mail->viewData['url'];

    expect($url)->toContain('senha/redefinir/signed-token')
        ->toContain('email=alvo%40example.test')
        ->toContain('signature=');

    expect($mail->viewData['expiresInMinutes'])->toBe(60)
        ->and($mail->viewData['recipientName'])->toBe($user->name);
});

it('produces a URL that the password.reset route accepts as valid', function () {
    $user = User::factory()->create();
    $notification = new PasswordResetRequested('valid-token');

    $url = $notification->toMail($user)->viewData['url'];

    $this->get($url)->assertOk();
});

it('produces a URL that rejects tampering with the email parameter', function () {
    $user = User::factory()->create(['email' => 'genuino@example.test']);
    $notification = new PasswordResetRequested('valid-token');

    $url = $notification->toMail($user)->viewData['url'];
    $tampered = str_replace('genuino%40example.test', 'invasor%40example.test', $url);

    $this->get($tampered)->assertStatus(403);
});
