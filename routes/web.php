<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [HelpdeskController::class, 'showLogin'])->name('login');
    Route::post('/login', [HelpdeskController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [HelpdeskController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [HelpdeskController::class, 'dashboard'])->name('dashboard');

    Route::middleware('admin')->group(function () {
        Route::get('/tickets/export', [TicketController::class, 'export'])->name('tickets.export');
    });

    Route::post('/tickets/{ticket}/note', [TicketController::class, 'addNote'])->name('tickets.note');
    Route::resource('tickets', TicketController::class);

    Route::middleware('admin')->group(function () {
        Route::resource('categories', CategoryController::class);
    });
});
