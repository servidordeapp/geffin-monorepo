<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the 404 page uses the application identity', function () {
    $this->get('/rota-que-nao-existe')
        ->assertNotFound()
        ->assertSee(config('app.name'))
        ->assertSee('Página não encontrada');
});

test('guests get a link back to the home page on error pages', function () {
    $this->get('/rota-que-nao-existe')
        ->assertNotFound()
        ->assertSee('Voltar para o início');
});

test('authenticated users get a link back to the dashboard on error pages', function () {
    $this->actingAs(User::factory()->create())
        ->get('/rota-que-nao-existe')
        ->assertNotFound()
        ->assertSee('Voltar para o dashboard')
        ->assertSee(route('dashboard'), false);
});
