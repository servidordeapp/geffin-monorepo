<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')->name('login');
    Route::livewire('/senha/esqueci', 'auth.forgot-password')->name('password.request');
    Route::livewire('/senha/redefinir/{token}', 'auth.reset-password')
        ->middleware('signed')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

Route::get('/', fn () => redirect()->route('login'));
