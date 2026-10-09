<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Promoter;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Facades\App;

it('stores and reads event content per locale with fallback', function (): void {
    $event = Event::factory()->create([
        'title' => ['pt' => 'Concerto de Natal', 'en' => 'Christmas Concert', 'fr' => 'Concert de Noël'],
    ]);

    App::setLocale('pt');
    expect($event->fresh()->title)->toBe('Concerto de Natal');

    App::setLocale('en');
    expect($event->fresh()->title)->toBe('Christmas Concert');

    App::setLocale('de'); // not translated -> falls back to default (pt)
    expect($event->fresh()->title)->toBe('Concerto de Natal');
});

it('seeds categories with the six supported locales', function (): void {
    $this->seed(CategorySeeder::class);
    $concertos = Category::where('slug', 'concertos')->first();

    expect($concertos->getTranslations('name'))->toHaveKeys(['pt', 'en', 'fr', 'es', 'de', 'it'])
        ->and($concertos->getTranslation('name', 'it'))->toBe('Concerti');
});

it('returns API event content in the requested locale', function (): void {
    $promoter = Promoter::factory()->create();
    $event = Event::factory()->published()->create([
        'promoter_id' => $promoter->id,
        'title' => ['pt' => 'Peça de Teatro', 'en' => 'Theatre Play'],
    ]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDay()]);

    $token = User::factory()->create()->createToken('t')->plainTextToken;

    $this->withToken($token)->getJson("/api/v1/events/{$event->slug}?lang=en")
        ->assertOk()->assertJsonPath('data.title', 'Theatre Play');

    $this->withToken($token)->getJson("/api/v1/events/{$event->slug}?lang=pt")
        ->assertOk()->assertJsonPath('data.title', 'Peça de Teatro');
});

it('resolves the locale from the ?lang query on the web', function (): void {
    $this->get('/events?lang=fr');
    expect(app()->getLocale())->toBe('fr');

    $this->get('/events?lang=zz'); // unsupported -> ignored, stays on session/default
    expect(app()->getLocale())->not->toBe('zz');
});
