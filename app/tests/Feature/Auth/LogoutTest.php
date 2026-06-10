<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard shows the logout button', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('logout'))
        ->assertSee('Sair');
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('logout invalidates the session', function () {
    $user = User::factory()->create();

    session(['some-key' => 'some-value']);

    $this->actingAs($user)->post(route('logout'));

    expect(session('some-key'))->toBeNull();
});

test('guests can not logout', function () {
    $this->post(route('logout'))
        ->assertRedirect(route('login'));
});
