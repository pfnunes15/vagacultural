<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Only register/login are public — everything else requires a token.
    Route::post('auth/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    // Authenticated (Sanctum token) — the whole data API lives here.
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

        // Events: list, calendar and detail all require authentication.
        Route::get('events', [EventController::class, 'index'])->name('api.events.index');
        Route::get('events/calendar', [EventController::class, 'calendar'])->name('api.events.calendar');
        Route::get('events/{event:slug}', [EventController::class, 'show'])->name('api.events.show');
    });
});
