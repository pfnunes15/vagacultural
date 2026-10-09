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
        // [slug base pt, translations [pt,en,fr,es,de,it], color, icon]
        $categories = [
            ['Concertos', ['pt' => 'Concertos', 'en' => 'Concerts', 'fr' => 'Concerts', 'es' => 'Conciertos', 'de' => 'Konzerte', 'it' => 'Concerti'], '#C8922A', 'music'],
            ['Festivais', ['pt' => 'Festivais', 'en' => 'Festivals', 'fr' => 'Festivals', 'es' => 'Festivales', 'de' => 'Festivals', 'it' => 'Festival'], '#8E44AD', 'festival'],
            ['Exposições', ['pt' => 'Exposições', 'en' => 'Exhibitions', 'fr' => 'Expositions', 'es' => 'Exposiciones', 'de' => 'Ausstellungen', 'it' => 'Mostre'], '#2E86C1', 'image'],
            ['Teatro', ['pt' => 'Teatro', 'en' => 'Theatre', 'fr' => 'Théâtre', 'es' => 'Teatro', 'de' => 'Theater', 'it' => 'Teatro'], '#C0392B', 'drama'],
            ['Workshops', ['pt' => 'Workshops', 'en' => 'Workshops', 'fr' => 'Ateliers', 'es' => 'Talleres', 'de' => 'Workshops', 'it' => 'Laboratori'], '#16A085', 'tools'],
            ['Cinema', ['pt' => 'Cinema', 'en' => 'Cinema', 'fr' => 'Cinéma', 'es' => 'Cine', 'de' => 'Kino', 'it' => 'Cinema'], '#2C3E50', 'film'],
            ['Dança', ['pt' => 'Dança', 'en' => 'Dance', 'fr' => 'Danse', 'es' => 'Danza', 'de' => 'Tanz', 'it' => 'Danza'], '#E67E22', 'dance'],
            ['Literatura', ['pt' => 'Literatura', 'en' => 'Literature', 'fr' => 'Littérature', 'es' => 'Literatura', 'de' => 'Literatur', 'it' => 'Letteratura'], '#7F8C8D', 'book'],
        ];

        foreach ($categories as $i => [$base, $names, $color, $icon]) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($base)],
                ['name' => $names, 'color' => $color, 'icon' => $icon, 'position' => $i, 'is_active' => true],
            );
        }
    }
}
