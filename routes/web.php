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
use App\Http\Controllers\Web\Auth\NewPasswordController;
use App\Http\Controllers\Web\Auth\PasswordResetLinkController;
use App\Http\Controllers\Web\Auth\RegisteredUserController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\My\AgendaController;
use App\Http\Controllers\Web\My\FavoriteController;
use App\Http\Controllers\Web\My\ProfileController;
use App\Http\Controllers\Web\Promoter\EventController as PromoterEventController;
use App\Http\Controllers\Web\PromoterRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('events.index'))->name('home');

// ---- Public events area (no login required) ----
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/calendar', [EventController::class, 'calendar'])->name('events.calendar');

// ---- Gated: event detail requires a registered, logged-in user ----
Route::get('/event/{event:slug}', [EventController::class, 'show'])
    ->middleware('auth')
    ->name('events.show');

// ---- Web session authentication ----
Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ---- Become a promoter (application) ----
Route::middleware('auth')->group(function (): void {
    Route::get('/become-promoter', [PromoterRequestController::class, 'create'])->name('promoter.apply');
    Route::post('/become-promoter', [PromoterRequestController::class, 'store']);
});

// ---- Email verification (registration confirmation) ----
Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');
});

// ---- Registered user: favorites & personal agenda ----
Route::middleware('auth')->prefix('my')->name('my.')->group(function (): void {
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites');
    Route::post('favorites/{event:slug}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('agenda', [AgendaController::class, 'index'])->name('agenda');
    Route::post('agenda/{event:slug}', [AgendaController::class, 'toggle'])->name('agenda.toggle');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// ---- Promoter / organization dashboard (create & manage own events) ----
Route::middleware(['auth', 'role:promoter,organization,admin'])
    ->prefix('dashboard')->name('dashboard.')->group(function (): void {
        Route::get('events', [PromoterEventController::class, 'index'])->name('events.index');
        Route::get('events/new', [PromoterEventController::class, 'create'])->name('events.create');
        Route::post('events', [PromoterEventController::class, 'store'])->name('events.store');
        Route::get('events/{event}/edit', [PromoterEventController::class, 'edit'])->name('events.edit');
        Route::put('events/{event}', [PromoterEventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [PromoterEventController::class, 'destroy'])->name('events.destroy');
    });

// ---- Stop impersonating: reachable while logged in AS the impersonated user ----
Route::post('/stop-impersonating', [ImpersonationController::class, 'stop'])
    ->middleware('auth')->name('impersonate.stop');

// ---- Admin control center ----
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users: roles, trust, activation, registration email, impersonation
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/roles', [AdminUserController::class, 'updateRoles'])->name('users.roles');
        Route::post('users/{user}/resend-email', [AdminUserController::class, 'resendEmail'])->name('users.resend');
        Route::post('users/{user}/trust', [AdminUserController::class, 'toggleTrust'])->name('users.trust');
        Route::post('users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('users.active');
        Route::post('users/{user}/impersonate', [ImpersonationController::class, 'start'])->name('users.impersonate');

        // Event moderation
        Route::get('events/pending', [EventModerationController::class, 'index'])->name('events.pending');
        Route::post('events/{event}/approve', [EventModerationController::class, 'approve'])->name('events.approve');
        Route::post('events/{event}/reject', [EventModerationController::class, 'reject'])->name('events.reject');

        // System / API status
        Route::get('system', [SystemStatusController::class, 'index'])->name('system.status');

        // Promoter onboarding requests
        Route::get('promoters/requests', [AdminPromoterRequestController::class, 'index'])->name('promoters.requests');
        Route::post('promoters/requests/{promoterRequest}/approve', [AdminPromoterRequestController::class, 'approve'])->name('promoters.requests.approve');
        Route::post('promoters/requests/{promoterRequest}/reject', [AdminPromoterRequestController::class, 'reject'])->name('promoters.requests.reject');
    });
