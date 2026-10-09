<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EmailLog;
use App\Models\User;
use App\Notifications\WeeklyDigest;
use App\Services\Recommendations\RecommendationService;
use Illuminate\Console\Command;

class SendWeeklyDigest extends Command
{
    protected $signature = 'mail:weekly-digest';

    protected $description = 'Send each opted-in user a personalized "what you can\'t miss this week" email';

    public function handle(RecommendationService $recommendations): int
    {
        $sent = 0;

        User::query()
            ->where('marketing_emails', true)
            ->whereNotNull('email_verified_at')
            ->chunkById(200, function ($users) use ($recommendations, &$sent): void {
                foreach ($users as $user) {
                    $events = $recommendations->for($user, 5);

                    if ($events->isEmpty()) {
                        continue;
                    }

                    $user->notify((new WeeklyDigest($events))->locale($user->locale ?? config('locales.default')));

                    EmailLog::create([
                        'user_id' => $user->id,
                        'type' => 'weekly_digest',
                        'recipient' => $user->email,
                        'subject' => 'O que não podes perder esta semana',
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $sent++;
                }
            });

        $this->info("Weekly digest sent to {$sent} user(s).");

        return self::SUCCESS;
    }
}
