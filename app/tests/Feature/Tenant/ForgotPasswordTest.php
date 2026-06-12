<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\Tenant\TenantUser;
use App\Notifications\Tenant\TenantResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->tenant->domains()->create(['domain' => 'acme.localhost']);
});

afterEach(function () {
    tenancy()->end();
    $this->tenant->delete();
});

test('forgot password screen can be rendered on the tenant domain', function () {
    $this->get('http://acme.localhost/esqueci-senha')
        ->assertOk()
        ->assertSeeLivewire('pages::tenant.auth.forgot-password');
});

test('tenant login screen links to the forgot password screen', function () {
    $this->get('http://acme.localhost/logar')
        ->assertOk()
        ->assertSee(route('tenant.password.request'));
});

test('email is required', function () {
    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.forgot-password')
        ->set('email', '')
        ->call('sendResetLink')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be a valid address', function () {
    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.forgot-password')
        ->set('email', 'not-an-email')
        ->call('sendResetLink')
        ->assertHasErrors(['email' => 'email']);
});

test('reset link is sent to a registered tenant user', function () {
    Notification::fake();

    tenancy()->initialize($this->tenant);

    $user = TenantUser::factory()->create();

    Livewire::test('pages::tenant.auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendResetLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertSentTo($user, TenantResetPasswordNotification::class);
});

test('reset link email is written in portuguese and links to the tenant reset screen', function () {
    Notification::fake();

    tenancy()->initialize($this->tenant);

    $user = TenantUser::factory()->create();

    Livewire::test('pages::tenant.auth.forgot-password')
        ->set('email', $user->email)
        ->call('sendResetLink');

    Notification::assertSentTo($user, TenantResetPasswordNotification::class, function (TenantResetPasswordNotification $notification) use ($user) {
        $mail = $notification->toMail($user);

        expect($mail->subject)->toBe('Redefinição de senha — '.config('app.name'))
            ->and($mail->actionText)->toBe('Redefinir senha')
            ->and($mail->actionUrl)->toEndWith(route('tenant.password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ], absolute: false));

        return true;
    });
});

test('unknown email shows the same confirmation without sending anything', function () {
    Notification::fake();

    tenancy()->initialize($this->tenant);

    Livewire::test('pages::tenant.auth.forgot-password')
        ->set('email', 'ghost@school.edu.br')
        ->call('sendResetLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertNothingSent();
});
