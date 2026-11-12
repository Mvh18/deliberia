<?php

use Illuminate\Support\Facades\Route;

// Toda la aplicación requiere sesión: la raíz lleva al panel (y los invitados al login).
Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
