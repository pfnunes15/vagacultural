<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\Auth\RegisteredUserController;
use App\Http\Controllers\Web\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('events.index'))->name('home');

// ---- Public events area (no login required) ----
Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
Route::get('/calendario', [EventController::class, 'calendar'])->name('events.calendar');

// ---- Gated: event detail requires a registered, logged-in user ----
Route::get('/evento/{event:slug}', [EventController::class, 'show'])
    ->middleware('auth')
    ->name('events.show');

// ---- Web session authentication ----
Route::middleware('guest')->group(function (): void {
    Route::get('/registar', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registar', [RegisteredUserController::class, 'store']);

    Route::get('/entrar', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/entrar', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/sair', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
