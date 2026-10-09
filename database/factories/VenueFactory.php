<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Venue> */
class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'municipality' => fake()->randomElement(['Funchal', 'Câmara de Lobos', 'Machico', 'Porto Santo']),
            'latitude' => fake()->latitude(32.6, 32.9),
            'longitude' => fake()->longitude(-17.3, -16.6),
            'is_active' => true,
        ];
    }
}
