<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PushSender;
use App\Models\EventOccurrence;
use App\Models\Ticket;
use App\Policies\TicketTypePolicy;
use App\Services\Push\Drivers\FcmPushSender;
use App\Services\Push\Drivers\LogPushSender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PushSender::class, function ($app) {
            return match (config('push.driver')) {
                'fcm' => new FcmPushSender,
                default => new LogPushSender,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ticketing authorization lives in TicketTypePolicy, keyed on the
        // occurrence/ticket passed to authorize('manage'|'checkIn', ...).
        Gate::policy(
            EventOccurrence::class,
            TicketTypePolicy::class,
        );
        Gate::policy(
            Ticket::class,
            TicketTypePolicy::class,
        );
    }
}
