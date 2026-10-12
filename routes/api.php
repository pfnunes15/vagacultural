<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\EngagementController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\HomeFeedController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Only register/login are public — everything else requires a token.
    Route::post('auth/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    // Authenticated (Sanctum token) — the whole data API lives here.
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

        // Profile
        Route::get('me/profile', [ProfileController::class, 'show'])->name('api.me.profile');
        Route::put('me/profile', [ProfileController::class, 'update'])->name('api.me.profile.update');
        Route::get('me/recommendations', [RecommendationController::class, 'index'])->name('api.me.recommendations');
        Route::get('me/feed', [HomeFeedController::class, 'index'])->name('api.me.feed');

        // Device registration for push notifications
        Route::post('me/devices', [DeviceController::class, 'store'])->name('api.me.devices.store');
        Route::delete('me/devices/{token}', [DeviceController::class, 'destroy'])->name('api.me.devices.destroy');

        // Ticketing (Phase 1)
        Route::get('occurrences/{occurrence}/ticket-types', [TicketController::class, 'types'])->name('api.occurrences.ticket-types');
        Route::post('occurrences/{occurrence}/register', [TicketController::class, 'register'])->name('api.occurrences.register');
        Route::get('me/tickets', [TicketController::class, 'mine'])->name('api.me.tickets');
        Route::post('tickets/{code}/check-in', [TicketController::class, 'checkIn'])->name('api.tickets.checkin');

        // Events: list, calendar and detail all require authentication.
        Route::get('events', [EventController::class, 'index'])->name('api.events.index');
        Route::get('events/calendar', [EventController::class, 'calendar'])->name('api.events.calendar');
        Route::get('events/{event:slug}', [EventController::class, 'show'])->name('api.events.show');

        // Registered user engagement
        Route::get('me/favorites', [EngagementController::class, 'favorites'])->name('api.me.favorites');
        Route::post('events/{event:slug}/favorite', [EngagementController::class, 'toggleFavorite'])->name('api.events.favorite');
        Route::get('me/agenda', [EngagementController::class, 'agenda'])->name('api.me.agenda');
        Route::post('events/{event:slug}/agenda', [EngagementController::class, 'toggleAgenda'])->name('api.events.agenda');
        Route::post('promoters/{promoter:slug}/follow', [EngagementController::class, 'followPromoter'])->name('api.promoters.follow');
        Route::post('organizations/{organization:slug}/follow', [EngagementController::class, 'followOrganization'])->name('api.organizations.follow');
    });
});
