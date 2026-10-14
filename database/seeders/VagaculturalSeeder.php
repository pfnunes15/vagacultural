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
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Imports a snapshot of real listings from the live vagacultural.pt — titles,
 * promoters, venues, dates and cover images — so staging shows real content and
 * real imagery for design review. Cover images are referenced by their public
 * URL on the source CDN; nothing is downloaded into the repo. Descriptions are
 * short placeholders — the full editorial import comes later.
 */
class VagaculturalSeeder extends Seeder
{
    private const IMG = 'https://wlhjsavrhndubrfonisd.supabase.co/storage/v1/object/public/event-images/';

    /** Source category label (EN, as shown on the live site) → local slug. */
    private const CATEGORY_MAP = [
        'Theatre' => 'teatro',
        'Exhibitions' => 'exposicoes',
        'Concerts' => 'concertos',
        'Clubbing' => 'noite',
        'Family' => 'familia',
        'Conferences' => 'conferencias',
        'Workshops' => 'workshops',
        'Well-being' => 'bem-estar',
    ];

    /** Best-effort geolocation of the source venues: name => [municipality, lat, lng]. */
    private const VENUES = [
        'Centro Cultural John dos Passos' => ['Ponta do Sol', 32.67900, -17.10100],
        'Rua do Bettencourt 10 - 2º andar' => ['Funchal', 32.64810, -16.90760],
        'Quinta da Rochinha' => ['Ponta do Sol', 32.68200, -17.10300],
        'Funchal' => ['Funchal', 32.64690, -16.90810],
        'R. do Castanheiro 31' => ['Funchal', 32.64870, -16.90730],
        'Largo do Socorro 1' => ['Funchal', 32.64800, -16.89950],
        'Parque Ecológico do Funchal' => ['Funchal', 32.70800, -16.88600],
        'Rua das Mercês' => ['Funchal', 32.64930, -16.91250],
        'Rua da Alfândega 9' => ['Funchal', 32.64660, -16.90650],
        'Estrada Monumental 175 177' => ['Funchal', 32.63700, -16.93300],
        'Casino da Madeira' => ['Funchal', 32.64160, -16.92740],
        'Centro Cívico do Estreito de Câmara de Lobos' => ['Câmara de Lobos', 32.66300, -16.98900],
        'Caminho da Quinta do Palheiro' => ['Funchal', 32.65300, -16.87700],
        'Colégio dos Jesuítas' => ['Funchal', 32.64890, -16.90800],
        'Tv. do Forte 8' => ['Funchal', 32.64660, -16.90550],
        'Rua Da Torrinha 69' => ['Funchal', 32.64400, -16.91600],
        'R. do Castelo 17' => ['Funchal', 32.64790, -16.90680],
    ];

    /** @var array<string, Promoter> */
    private array $promoterCache = [];

    /** @var array<string, Venue> */
    private array $venueCache = [];

    public function run(): void
    {
        $year = 2026;

        /** @var list<array{0:string,1:string,2:string,3:string,4:string,5:string}> $rows */
        $rows = json_decode(self::DATA, true, flags: JSON_THROW_ON_ERROR);

        foreach ($rows as $i => [$imgFile, $catLabel, $title, $promoterName, $dateText, $venueName]) {
            [$start, $end] = $this->parseDates($dateText, $year);
            if ($start === null) {
                continue;
            }

            $promoter = $this->promoter($promoterName);
            $venue = $this->venue($venueName);

            $event = Event::query()->create([
                'promoter_id' => $promoter->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
                'summary' => $promoterName . ' · Madeira',
                'description' => $title . ' — por ' . $promoterName . '. Mais informações em breve.',
                'cover_image_path' => self::IMG . $imgFile,
                'status' => EventStatus::Published->value,
                'is_featured' => $i % 5 === 0,
                'is_free' => true,
                'published_at' => now()->subDay(),
            ]);

            if ($cat = Category::query()->where('slug', self::CATEGORY_MAP[$catLabel] ?? null)->first()) {
                $event->categories()->attach($cat, ['is_primary' => true]);
            }
            $event->tags()->sync([$this->tag($catLabel)]);

            $occ = EventOccurrence::query()->create([
                'event_id' => $event->id,
                'venue_id' => $venue->id,
                'starts_at' => $start,
                'ends_at' => $end ?? $start->copy()->addHours(2),
                'status' => 'scheduled',
            ]);

            TicketType::create([
                'event_occurrence_id' => $occ->id,
                'name' => 'Entrada livre',
                'price' => 0,
                'capacity' => 200,
                'position' => 0,
            ]);
        }
    }

    /**
     * Parse the card's date string (e.g. "Friday, 16/10 | 21:00" or a
     * "11/09 - 12/11 | 10:00" range) into a start/end. Fully past listings are
     * dropped; still-running ranges are surfaced as happening tomorrow so they
     * show up as upcoming on staging.
     *
     * @return array{0:?Carbon, 1:?Carbon}
     */
    private function parseDates(string $text, int $year): array
    {
        if (! preg_match_all('/(\d{1,2})\/(\d{1,2})/', $text, $m, PREG_SET_ORDER)) {
            return [null, null];
        }

        preg_match('/(\d{1,2}):(\d{2})/', $text, $t);
        $hour = isset($t[1]) ? (int) $t[1] : 19;
        $minute = isset($t[2]) ? (int) $t[2] : 0;

        $make = fn (array $d): Carbon => Carbon::create($year, (int) $d[2], (int) $d[1], $hour, $minute);
        $start = $make($m[0]);
        $end = count($m) > 1 ? $make($m[count($m) - 1]) : null;

        $today = now()->startOfDay();

        if (($end ?? $start)->copy()->startOfDay()->lt($today)) {
            return [null, null]; // over already
        }

        if ($start->copy()->startOfDay()->lt($today)) {
            $start = now()->addDay()->setTime($hour, $minute); // ongoing → show soon
        }

        return [$start, $end];
    }

    private function promoter(string $name): Promoter
    {
        $slug = Str::slug($name);

        return $this->promoterCache[$slug] ??= Promoter::query()->where('slug', $slug)->first()
            ?? Promoter::factory()->create(['name' => $name, 'slug' => $slug, 'auto_publish' => true]);
    }

    private function venue(string $name): Venue
    {
        $slug = Str::slug($name);
        [$municipality, $lat, $lng] = self::VENUES[$name] ?? ['Funchal', null, null];

        return $this->venueCache[$slug] ??= Venue::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'municipality' => $municipality, 'latitude' => $lat, 'longitude' => $lng, 'is_active' => true],
        );
    }

    private function tag(string $catLabel): int
    {
        $slug = self::CATEGORY_MAP[$catLabel] ?? Str::slug($catLabel);

        return Tag::firstOrCreate(['slug' => $slug], ['name' => $slug])->id;
    }

    private const DATA = <<<'JSON'
[["1790776258129-28jdx8.webp", "Theatre", "Festival Avesso", "Associação Avesso", "Wednesday, 14/10 | 21:00", "Centro Cultural John dos Passos"], ["1791315108529-ugm0v.webp", "Theatre", "Bom Filho a Casa Torna", "Associação Avesso", "14/10 - 15/10 | 21:00", "Centro Cultural John dos Passos"], ["1788858356335-pkvmmq.webp", "Exhibitions", "Exposição Jovens Talentos FZC - Artes Visuais", "Fundação Cecilia Zino", "11/09 - 12/11 | 10:00", "Rua do Bettencourt 10 - 2º andar"], ["1786216215598-q2nk3r.webp", "Concerts", "Concertos L", "Estalagem da Ponta do Sol", "19/08 - 21/10 | 22:00", "Quinta da Rochinha"], ["1775060427152-cdavuf.jpg", "Concerts", "Duo Bandonica Fala-me de Música", "Associação de Bandolins da Madeira", "18/04 - 05/12 | 19:00", "Funchal"], ["1790342266598-mewjps.jpg", "Concerts", "À beira do InFinito", "Associação InFinito - Arte e Comunidade", "Thursday, 15/10 | 17:30", "R. do Castanheiro 31"], ["1791903992677-lzew4.webp", "Clubbing", "The Purple Fridays", "Estalagem da Ponta do Sol", "Friday, 16/10 | 19:00", "Quinta da Rochinha"], ["1791315515949-8tghe6.webp", "Theatre", "Casa de Bonecas", "Associação Avesso", "Friday, 16/10 | 21:00", "Centro Cultural John dos Passos"], ["1791980414524-bh8z49.webp", "Concerts", "Pedro Branco", "A. CULTURAL DO SOCORRO", "Friday, 16/10 | 21:00", "Largo do Socorro 1"], ["1791901488722-gsmz5.webp", "Family", "Aventureiros à descoberta", "Rebentos à descoberta", "17/10 - 24/10 | 10:00", "Parque Ecológico do Funchal"], ["1790757936694-ui78rs.webp", "Family", "Workshop de Sabonetes e Pinturas Naturais - HALLOWEEN", "KHÔRA", "Saturday, 17/10 | 10:30", "Rua das Mercês"], ["1791984118468-6zs34c.webp", "Conferences", "Testemunhos de Encantar", "Barceló Funchal Oldtown", "Saturday, 17/10 | 16:00", "Rua da Alfândega 9"], ["1791315594298-g5vh09.webp", "Theatre", "AMOR E REDES SOCIAIS", "Associação Avesso", "Saturday, 17/10 | 21:00", "Centro Cultural John dos Passos"], ["1788795490400-ofc8v.jpg", "Workshops", "AI DIVE FOR BUSINESS", "C.A.O.S.", "20/10 - 23/10 | 09:00", "Estrada Monumental 175 177"], ["1791907569223-0cx0ph.webp", "Clubbing", "The Phantom of Copacabana", "Casino da Madeira", "31/10 - 01/11 | 23:00", "Casino da Madeira"], ["1790175245139-w7284g.jpg", "Workshops", "Oficina de Bordado Madeira", "Bailha", "Saturday, 07/11 | 10:00", "Centro Cívico do Estreito de Câmara de Lobos"], ["1789391421126-wk4nvu.webp", "Workshops", "Oficina de Feltragem: Um Brinquedo para o Animal de Estimação", "Oh Lã Lã Fiber Lab — laboratório de práticas têxteis", "Saturday, 07/11 | 14:30", "Caminho da Quinta do Palheiro"], ["1789392211359-nl63pn.webp", "Workshops", "Como Ler um Velo: Primeiros Passos na Escolha da Lã da Madeira", "Oh Lã Lã Fiber Lab — laboratório de práticas têxteis", "Sunday, 08/11 | 10:00", "Caminho da Quinta do Palheiro"], ["1791884945390-eryra.webp", "Well-being", "MADEIRA WOOL SOCIETY", "Oh Lã Lã Fiber Lab — laboratório de práticas têxteis", "Saturday, 14/11 | 10:00", "Caminho da Quinta do Palheiro"], ["1789546719909-30t8oh.webp", "Clubbing", "30 Dez 2026", "Castanheiro Boutique Hotel", "Wednesday, 30/12 | 23:00", "Colégio dos Jesuítas"], ["1790342110985-qle1yxe.jpg", "Concerts", "InFinito - Festival Artístico Multidisciplinar", "Associação InFinito - Arte e Comunidade", "Saturday, 17/10 | 15:00", "Tv. do Forte 8"], ["1790686695501-5p3q8.webp", "Concerts", "O Bosque Encantado - Practical Magic Edition", "Quinta palmeira Gardens", "Saturday, 17/10 | 17:00", "Rua Da Torrinha 69"], ["1786899413510-a5iwok.webp", "Concerts", "Concertos L - Três Tristes Tigres", "Estalagem da Ponta do Sol", "Saturday, 17/10 | 22:00", "Quinta da Rochinha"], ["1790342278969-v9mbii.jpg", "Concerts", "Olhar o InFinito", "Associação InFinito - Arte e Comunidade", "Wednesday, 21/10 | 17:30", "R. do Castelo 17"], ["1786899522050-uj2rxo.webp", "Concerts", "Concertos L - Juliana Linhares - encerramento da temporada", "Estalagem da Ponta do Sol", "Wednesday, 21/10 | 22:00", "Quinta da Rochinha"], ["1790342252481-xakev8.jpg", "Concerts", "Para lá do InFinito", "Associação InFinito - Arte e Comunidade", "Friday, 23/10 | 19:00", "Largo do Socorro 1"]]
JSON;
}
