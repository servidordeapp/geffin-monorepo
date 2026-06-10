<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::auth.login')->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard');
});
