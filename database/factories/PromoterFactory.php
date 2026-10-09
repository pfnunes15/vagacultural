<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Promoter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Promoter> */
class PromoterFactory extends Factory
{
    protected $model = Promoter::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'is_verified' => true,
            'is_active' => true,
        ];
    }
}
