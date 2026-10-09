<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('mail:weekly-digest')->weeklyOn(4, '09:00'); // Thursdays 09:00
Schedule::command('mail:promoter-nudge')->monthlyOn(1, '10:00');
