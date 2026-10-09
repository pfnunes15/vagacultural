<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\EventModerationController;
use App\Http\Controllers\Web\Admin\ImpersonationController;
use App\Http\Controllers\Web\Admin\PromoterRequestController as AdminPromoterRequestController;
use App\Http\Controllers\Web\Admin\SystemStatusController;
use App\Http\Controllers\Web\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\Auth\EmailVerificationController;
use App\Http\Controllers\Web\Auth\RegisteredUserController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\Promoter\EventController as PromoterEventController;
use App\Http\Controllers\Web\PromoterRequestController;
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

// ---- Become a promoter (application) ----
Route::middleware('auth')->group(function (): void {
    Route::get('/promotor/candidatura', [PromoterRequestController::class, 'create'])->name('promoter.apply');
    Route::post('/promotor/candidatura', [PromoterRequestController::class, 'store']);
});

// ---- Email verification (registration confirmation) ----
Route::middleware('auth')->group(function (): void {
    Route::get('/email/verificar', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verificar/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/reenviar', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');
});

// ---- Promoter / organization dashboard (create & manage own events) ----
Route::middleware(['auth', 'role:promoter,organization,admin'])
    ->prefix('painel')->name('painel.')->group(function (): void {
        Route::get('eventos', [PromoterEventController::class, 'index'])->name('eventos.index');
        Route::get('eventos/novo', [PromoterEventController::class, 'create'])->name('eventos.create');
        Route::post('eventos', [PromoterEventController::class, 'store'])->name('eventos.store');
    });

// ---- Stop impersonating: reachable while logged in AS the impersonated user ----
Route::post('/parar-impersonacao', [ImpersonationController::class, 'stop'])
    ->middleware('auth')->name('impersonate.stop');

// ---- Admin control center ----
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users: roles, trust, activation, registration email, impersonation
        Route::get('utilizadores', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('utilizadores/{user}/papeis', [AdminUserController::class, 'updateRoles'])->name('users.roles');
        Route::post('utilizadores/{user}/reenviar-email', [AdminUserController::class, 'resendEmail'])->name('users.resend');
        Route::post('utilizadores/{user}/confianca', [AdminUserController::class, 'toggleTrust'])->name('users.trust');
        Route::post('utilizadores/{user}/estado', [AdminUserController::class, 'toggleActive'])->name('users.active');
        Route::post('utilizadores/{user}/impersonar', [ImpersonationController::class, 'start'])->name('users.impersonate');

        // Event moderation
        Route::get('eventos/pendentes', [EventModerationController::class, 'index'])->name('eventos.pending');
        Route::post('eventos/{event}/aprovar', [EventModerationController::class, 'approve'])->name('eventos.approve');
        Route::post('eventos/{event}/rejeitar', [EventModerationController::class, 'reject'])->name('eventos.reject');

        // System / API status
        Route::get('sistema', [SystemStatusController::class, 'index'])->name('system.status');

        // Promoter onboarding requests
        Route::get('promotores/pedidos', [AdminPromoterRequestController::class, 'index'])->name('promoters.requests');
        Route::post('promotores/pedidos/{promoterRequest}/aprovar', [AdminPromoterRequestController::class, 'approve'])->name('promoters.requests.approve');
        Route::post('promotores/pedidos/{promoterRequest}/recusar', [AdminPromoterRequestController::class, 'reject'])->name('promoters.requests.reject');
    });
