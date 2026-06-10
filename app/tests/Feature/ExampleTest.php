<?php

declare(strict_types=1);

use App\Models\User;

test('the application returns a successful response', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('dashboard'));
});

test('unauthenticated user is redirected to login', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});
