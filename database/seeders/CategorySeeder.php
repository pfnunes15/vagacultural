<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Concertos', 'Concerts', '#C8922A', 'music'],
            ['Festivais', 'Festivals', '#8E44AD', 'festival'],
            ['Exposições', 'Exhibitions', '#2E86C1', 'image'],
            ['Teatro', 'Theatre', '#C0392B', 'drama'],
            ['Workshops', 'Workshops', '#16A085', 'tools'],
            ['Cinema', 'Cinema', '#2C3E50', 'film'],
            ['Dança', 'Dance', '#E67E22', 'dance'],
            ['Literatura', 'Literature', '#7F8C8D', 'book'],
        ];

        foreach ($categories as $i => [$name, $nameEn, $color, $icon]) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'name_en' => $nameEn,
                    'color' => $color,
                    'icon' => $icon,
                    'position' => $i,
                    'is_active' => true,
                ],
            );
        }
    }
}
