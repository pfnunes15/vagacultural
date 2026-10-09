<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EmailLog;
use App\Models\Promoter;
use App\Notifications\PromoterNudge;
use Illuminate\Console\Command;

class SendPromoterNudge extends Command
{
    protected $signature = 'mail:promoter-nudge';

    protected $description = 'Encourage active promoters with no upcoming events to publish';

    public function handle(): int
    {
        $sent = 0;

        Promoter::query()
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->whereDoesntHave('events', fn ($q) => $q->whereHas(
                'occurrences',
                fn ($o) => $o->where('starts_at', '>=', now()),
            ))
            ->with('user')
            ->chunkById(200, function ($promoters) use (&$sent): void {
                foreach ($promoters as $promoter) {
                    $user = $promoter->user;

                    if ($user === null || ! $user->marketing_emails) {
                        continue;
                    }

                    $user->notify((new PromoterNudge($promoter->name))->locale($user->locale ?? config('locales.default')));

                    EmailLog::create([
                        'user_id' => $user->id,
                        'type' => 'promoter_nudge',
                        'recipient' => $user->email,
                        'subject' => 'Publica os teus próximos eventos',
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $sent++;
                }
            });

        $this->info("Promoter nudge sent to {$sent} promoter(s).");

        return self::SUCCESS;
    }
}
