<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\Tag;
use App\Models\TicketType;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Realistic sample events across Madeira & Porto Santo, so the restyled pages
 * look full. Not real listings — placeholder content for development/demo.
 */
class DemoEventsSeeder extends Seeder
{
    public function run(): void
    {
        $venues = $this->venues();
        $promoter = Promoter::query()->where('auto_publish', true)->first()
            ?? Promoter::factory()->create(['auto_publish' => true]);

        $cat = fn (string $slug) => Category::query()->where('slug', $slug)->first();

        // [title, category slug, venue key, days from now, hour, free?, price, featured?, min_age, tags]
        $events = [
            ['Noite de Fado no Beco', 'concertos', 'se', 0, '21:30', false, 18, true, null, ['fado', 'intimista']],
            ['Dia Internacional do Jazz', 'concertos', 'conservatorio', 2, '19:30', false, 12, true, null, ['jazz', 'ao vivo']],
            ['Orquestra Clássica — Noites de Outono', 'concertos', 'baltazar', 3, '21:00', false, 15, true, null, ['clássica', 'orquestra']],
            ['Raízes do Atlântico', 'festivais', 'povo', 5, '17:00', false, 20, true, null, ['músicas do mundo', 'ar livre']],
            ['Cartografias do Atlântico', 'exposicoes', 'mudas', 1, '10:00', true, 0, false, null, ['arte contemporânea']],
            ['A Tempestade — Teatro Bolo do Caco', 'teatro', 'baltazar', 6, '21:30', false, 9, false, 12, ['teatro', 'clássico']],
            ['Corpo-Ilha', 'danca', 'johndospassos', 4, '21:00', false, 10, false, null, ['dança contemporânea']],
            ['Bordado Madeira para principiantes', 'workshops', 'ivbam', 2, '17:00', false, 15, false, null, ['artesanato', 'workshop']],
            ['Retrospetiva Manoel de Oliveira', 'cinema', 'tef', 0, '19:30', false, 4, false, 12, ['cinema', 'cineclube']],
            ['Feira do Livro do Funchal', 'literatura', 'arriaga', 7, '10:00', true, 0, false, null, ['livros', 'ar livre']],
            ['Entardecer na Fontinha', 'concertos', 'portosanto', 8, '18:00', false, 10, false, null, ['pôr do sol']],
            ['Matinê de Marionetas', 'teatro', 'machico', 9, '11:00', false, 5, false, null, ['família']],
        ];

        foreach ($events as [$title, $catSlug, $venueKey, $days, $hour, $free, $price, $featured, $minAge, $tags]) {
            $event = Event::query()->create([
                'promoter_id' => $promoter->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::lower(Str::random(4)),
                'summary' => 'Um evento cultural na ilha da Madeira. ' . $title . '.',
                'description' => "Descrição de demonstração para \"{$title}\". Conteúdo real será importado mais tarde.",
                'status' => EventStatus::Published->value,
                'is_featured' => $featured,
                'is_free' => $free,
                'price_from' => $free ? null : $price,
                'min_age' => $minAge,
                'published_at' => now()->subDay(),
            ]);

            if ($c = $cat($catSlug)) {
                $event->categories()->attach($c, ['is_primary' => true]);
            }
            $event->tags()->sync($this->tagIds($tags));

            [, $h, $m] = array_pad(array_merge([''], explode(':', $hour)), 3, '00');
            $occ = EventOccurrence::query()->create([
                'event_id' => $event->id,
                'venue_id' => $venues[$venueKey]->id,
                'starts_at' => now()->addDays($days)->setTime((int) $h, (int) $m),
                'ends_at' => now()->addDays($days)->setTime((int) $h + 2, (int) $m),
                'status' => 'scheduled',
            ]);

            if (! $free) {
                TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Geral', 'price' => $price, 'capacity' => 120, 'position' => 0]);
                TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Estudante', 'price' => max(0, $price - 5), 'capacity' => 40, 'position' => 1]);
            } else {
                TicketType::create(['event_occurrence_id' => $occ->id, 'name' => 'Entrada livre', 'price' => 0, 'capacity' => 300, 'position' => 0]);
            }
        }
    }

    /** @return array<string, Venue> */
    private function venues(): array
    {
        // [name, municipality, latitude, longitude]
        $defs = [
            'se' => ['Beco da Sé', 'Funchal', 32.64790, -16.90950],
            'conservatorio' => ['Conservatório — Escola das Artes', 'Funchal', 32.64900, -16.91200],
            'baltazar' => ['Teatro Municipal Baltazar Dias', 'Funchal', 32.64780, -16.91100],
            'povo' => ['Praça do Povo', 'Funchal', 32.64200, -16.92300],
            'mudas' => ['MUDAS — Museu de Arte Contemporânea', 'Calheta', 32.71800, -17.17700],
            'johndospassos' => ['Centro Cultural John dos Passos', 'Ponta do Sol', 32.67900, -17.10100],
            'ivbam' => ['IVBAM — Mercado dos Lavradores', 'Funchal', 32.64700, -16.90600],
            'tef' => ['Teatro Experimental do Funchal', 'Funchal', 32.64850, -16.90850],
            'arriaga' => ['Avenida Arriaga', 'Funchal', 32.64700, -16.91500],
            'portosanto' => ['Centro de Cultura e Congressos', 'Porto Santo', 33.06400, -16.33800],
            'machico' => ['Fórum Machico', 'Machico', 32.72000, -16.76800],
        ];

        $out = [];
        foreach ($defs as $key => [$name, $municipality, $lat, $lng]) {
            $out[$key] = Venue::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'municipality' => $municipality, 'latitude' => $lat, 'longitude' => $lng, 'is_active' => true],
            );
        }

        return $out;
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    private function tagIds(array $names): array
    {
        return collect($names)->map(fn (string $n): int => Tag::firstOrCreate(
            ['slug' => Str::slug($n)],
            ['name' => $n],
        )->id)->all();
    }
}
