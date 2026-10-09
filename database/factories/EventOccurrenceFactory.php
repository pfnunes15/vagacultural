<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OccurrenceStatus;
use App\Models\Event;
use App\Models\EventOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventOccurrence> */
class EventOccurrenceFactory extends Factory
{
    protected $model = EventOccurrence::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+2 months');

        return [
            'event_id' => Event::factory(),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+2 hours'),
            'status' => OccurrenceStatus::Scheduled,
        ];
    }
}
