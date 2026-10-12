<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PushSender;
use App\Services\Push\Drivers\FcmPushSender;
use App\Services\Push\Drivers\LogPushSender;
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
        //
    }
}
