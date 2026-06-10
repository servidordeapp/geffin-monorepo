<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('forgot password screen can be rendered', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSeeLivewire('pages::auth.forgot-password');
});

test('login screen links to the forgot password screen', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('password.request'));
});

test('email is required', function () {
    Livewire::test('pages::auth.forgot-password')
        ->set('email', '')
        ->call('sendResetLink')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be a valid address', function () {
    Livewire::test('pages::auth.forgot-password')
        ->set('email', 'not-an-email')
        ->call('sendResetLink')
        ->assertHasErrors(['email' => 'email']);
});

test('reset link is sent to a registered email', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test('pages::auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendResetLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset link email is written in portuguese and links to the reset screen', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test('pages::auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
        $mail = $notification->toMail($user);

        expect($mail->subject)->toBe('Redefinição de senha — '.config('app.name'))
            ->and($mail->actionText)->toBe('Redefinir senha')
            ->and($mail->actionUrl)->toBe(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]));

        return true;
    });
});

test('unknown email shows the same confirmation without sending anything', function () {
    Notification::fake();

    Livewire::test('pages::auth.forgot-password')
        ->set('email', 'ghost@school.edu.br')
        ->call('sendResetLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertNothingSent();
});
