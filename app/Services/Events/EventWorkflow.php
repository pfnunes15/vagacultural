<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Event publication lifecycle.
 *
 * Single responsibility: owns the state transitions of an event
 * (submit -> published|pending -> approved/rejected) and the trust rule
 * that decides whether a promoter's submission goes live immediately.
 */
class EventWorkflow
{
    /**
     * Status a new submission lands in, based on the promoter's trust level.
     * Trusted promoters (admin-set `auto_publish`) publish immediately;
     * everyone else waits for admin approval.
     */
    public function initialStatusFor(Promoter $promoter): EventStatus
    {
        return $promoter->auto_publish ? EventStatus::Published : EventStatus::Pending;
    }

    /**
     * Create and submit an event on behalf of a promoter.
     *
     * @param  array<string, mixed>  $attributes  validated event fields
     * @param  list<int>  $categoryIds
     * @param  list<array<string, mixed>>  $occurrences
     */
    public function submit(
        Promoter $promoter,
        array $attributes,
        array $categoryIds,
        array $occurrences,
        User $submitter,
    ): Event {
        return DB::transaction(function () use ($promoter, $attributes, $categoryIds, $occurrences, $submitter): Event {
            $status = $this->initialStatusFor($promoter);

            $event = $promoter->events()->create([
                ...$attributes,
                'slug' => $this->uniqueSlug($attributes['title']),
                'status' => $status,
                'submitted_by' => $submitter->id,
                'published_at' => $status === EventStatus::Published ? now() : null,
            ]);

            if ($categoryIds !== []) {
                $primary = $categoryIds[0];
                $event->categories()->sync(
                    collect($categoryIds)->mapWithKeys(
                        fn (int $id): array => [$id => ['is_primary' => $id === $primary]],
                    )->all(),
                );
            }

            foreach ($occurrences as $occurrence) {
                $event->occurrences()->create($occurrence);
            }

            return $event;
        });
    }

    /** Admin approves a pending event. */
    public function approve(Event $event): Event
    {
        $event->update([
            'status' => EventStatus::Published,
            'published_at' => $event->published_at ?? now(),
        ]);

        return $event;
    }

    /** Admin rejects a pending event. */
    public function reject(Event $event): Event
    {
        $event->update(['status' => EventStatus::Rejected]);

        return $event;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (Event::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
