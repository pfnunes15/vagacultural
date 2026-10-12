<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Contracts\PushSender;
use App\Models\Event;
use App\Models\Follow;
use App\Models\User;

/**
 * When an event goes live, push a notification to users who follow its
 * promoter (or the promoter's organization).
 */
class NotifyFollowersOfPublishedEvent
{
    public function __construct(private readonly PushSender $push) {}

    public function handle(Event $event): int
    {
        $promoter = $event->promoter;

        if ($promoter === null) {
            return 0;
        }

        $followerIds = Follow::query()
            ->where(function ($q) use ($promoter): void {
                $q->where('followable_type', $promoter->getMorphClass())
                    ->where('followable_id', $promoter->id);
            })
            ->when($promoter->organization_id, function ($q) use ($promoter): void {
                $org = $promoter->organization;
                if ($org !== null) {
                    $q->orWhere(function ($w) use ($org): void {
                        $w->where('followable_type', $org->getMorphClass())
                            ->where('followable_id', $org->id);
                    });
                }
            })
            ->pluck('user_id')
            ->unique();

        if ($followerIds->isEmpty()) {
            return 0;
        }

        $sent = 0;

        User::query()
            ->whereIn('id', $followerIds)
            ->whereHas('deviceTokens')
            ->each(function (User $user) use ($event, $promoter, &$sent): void {
                $title = $event->getTranslation('title', $user->locale ?? config('locales.default'), false) ?: $event->title;

                $sent += $this->push->toUser(
                    $user,
                    $promoter->name,
                    'Novo evento: ' . $title,
                    ['type' => 'event', 'slug' => $event->slug],
                );
            });

        return $sent;
    }
}
