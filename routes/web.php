<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Admin\EventModerationController;
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

// ---- Promoter / organization dashboard (create & manage own events) ----
Route::middleware(['auth', 'role:promoter,organization,admin'])
    ->prefix('painel')->name('painel.')->group(function (): void {
        Route::get('eventos', [App\Http\Controllers\Web\Promoter\EventController::class, 'index'])->name('eventos.index');
        Route::get('eventos/novo', [App\Http\Controllers\Web\Promoter\EventController::class, 'create'])->name('eventos.create');
        Route::post('eventos', [App\Http\Controllers\Web\Promoter\EventController::class, 'store'])->name('eventos.store');
    });

// ---- Admin moderation (approve/reject pending events) ----
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('eventos/pendentes', [EventModerationController::class, 'index'])->name('eventos.pending');
        Route::post('eventos/{event}/aprovar', [EventModerationController::class, 'approve'])->name('eventos.approve');
        Route::post('eventos/{event}/rejeitar', [EventModerationController::class, 'reject'])->name('eventos.reject');
    });
