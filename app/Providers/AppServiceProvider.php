<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\PushSender;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Ticket;
use App\Policies\TicketTypePolicy;
use App\Services\Payments\Drivers\FakePaymentGateway;
use App\Services\Push\Drivers\FcmPushSender;
use App\Services\Push\Drivers\LogPushSender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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

        $this->app->bind(PaymentGateway::class, function ($app) {
            return match (config('ticketing.gateway', 'fake')) {
                default => new FakePaymentGateway,
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

        // Share the current user's favourited event ids with every view (one
        // lazy query per request) so event cards render the right saved state.
        View::composer('*', function ($view): void {
            static $ids = null;
            if ($ids === null) {
                $user = auth()->user();
                $ids = $user
                    ? $user->favorites()
                        ->where('favoritable_type', (new Event)->getMorphClass())
                        ->pluck('favoritable_id')->all()
                    : [];
            }
            $view->with('myFavoriteEventIds', $ids);
        });
    }
}
