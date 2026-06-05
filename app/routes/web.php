<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/login', 'pages::auth.login')->name('login');

Route::get('/dashboard', function () {
    return 'Dashboard';
})->name('dashboard');
