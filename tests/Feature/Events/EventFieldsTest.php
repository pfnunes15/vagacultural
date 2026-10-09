<?php

declare(strict_types=1);

use App\Enums\AgeRating;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\Events\EventWorkflow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function trustedPromoterUser(): array
{
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => UserRole::Promoter->value]);
    $promoter = Promoter::factory()->create(['user_id' => $user->id, 'auto_publish' => true]);

    return [$user, $promoter];
}

it('stores tags, age-based ticket tiers and min age via the workflow', function (): void {
    [$user, $promoter] = trustedPromoterUser();

    $event = app(EventWorkflow::class)->submit(
        $promoter,
        ['title' => 'Concerto Família', 'min_age' => 6, 'is_free' => false],
        [],
        [['starts_at' => now()->addWeek()]],
        $user,
        ['ar livre', 'família', 'ar livre'],
        [
            ['name' => 'Adulto', 'price' => 12, 'min_age' => 18],
            ['name' => 'Criança', 'price' => 5, 'min_age' => 0, 'max_age' => 12],
        ],
    );

    expect($event->tags)->toHaveCount(2) // deduplicated
        ->and($event->min_age)->toBe(AgeRating::SixPlus)
        ->and($event->ticketTiers)->toHaveCount(2)
        ->and($event->ticketTiers->firstWhere('name', 'Criança')->max_age)->toBe(12)
        ->and(Tag::count())->toBe(2);
});

it('supports a single date, several dates and a continuous range', function (): void {
    [$user, $promoter] = trustedPromoterUser();

    // several separate dates
    $multi = app(EventWorkflow::class)->submit($promoter, ['title' => 'Ciclo de Cinema'], [], [
        ['starts_at' => now()->addDays(1)],
        ['starts_at' => now()->addDays(8)],
        ['starts_at' => now()->addDays(15)],
    ], $user);
    expect($multi->occurrences)->toHaveCount(3);

    // continuous range (exhibition)
    $exhibition = app(EventWorkflow::class)->submit($promoter, ['title' => 'Exposição'], [], [
        ['starts_at' => now()->addDay(), 'ends_at' => now()->addMonth(), 'is_all_day' => true],
    ], $user);
    $occ = $exhibition->occurrences->first();
    expect($occ->is_all_day)->toBeTrue()
        ->and($occ->ends_at->gt($occ->starts_at))->toBeTrue();
});

it('models an online occurrence with a link', function (): void {
    [$user, $promoter] = trustedPromoterUser();

    $event = app(EventWorkflow::class)->submit($promoter, ['title' => 'Conversa Online'], [], [
        ['starts_at' => now()->addDay(), 'is_online' => true, 'online_url' => 'https://zoom.us/j/123'],
    ], $user);

    $occ = $event->occurrences->first();
    expect($occ->is_online)->toBeTrue()
        ->and($occ->online_url)->toBe('https://zoom.us/j/123')
        ->and($occ->locationLabel())->toBe('Online');
});

it('accepts a cover image with exact Instagram 1080x1350 dimensions', function (): void {
    Storage::fake('public');
    [$user, $promoter] = trustedPromoterUser();
    $category = Category::factory()->create();

    $this->actingAs($user)->post('/painel/eventos', [
        'promoter_id' => $promoter->id,
        'title' => 'Com Capa Correta',
        'categories' => [$category->id],
        'is_free' => '1',
        'cover_image' => UploadedFile::fake()->image('capa.jpg', 1080, 1350),
        'occurrences' => [['starts_at' => now()->addDay()->format('Y-m-d H:i:s')]],
    ])->assertRedirect(route('painel.eventos.index'));

    $event = Event::latest('id')->first();
    expect($event->title)->toBe('Com Capa Correta')
        ->and($event->cover_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($event->cover_image_path);
});

it('rejects a cover image with the wrong dimensions', function (): void {
    Storage::fake('public');
    [$user, $promoter] = trustedPromoterUser();
    $category = Category::factory()->create();

    $this->actingAs($user)->post('/painel/eventos', [
        'promoter_id' => $promoter->id,
        'title' => 'Capa Errada',
        'categories' => [$category->id],
        'is_free' => '1',
        'cover_image' => UploadedFile::fake()->image('capa.jpg', 1080, 1080),
        'occurrences' => [['starts_at' => now()->addDay()->format('Y-m-d H:i:s')]],
    ])->assertSessionHasErrors('cover_image');

    $this->assertDatabaseMissing('events', ['title' => 'Capa Errada']);
});

it('requires a link when an occurrence is online', function (): void {
    [$user, $promoter] = trustedPromoterUser();
    $category = Category::factory()->create();

    $this->actingAs($user)->post('/painel/eventos', [
        'promoter_id' => $promoter->id,
        'title' => 'Online sem link',
        'categories' => [$category->id],
        'is_free' => '1',
        'occurrences' => [['starts_at' => now()->addDay()->format('Y-m-d H:i:s'), 'is_online' => '1']],
    ])->assertSessionHasErrors('occurrences.0.online_url');
});
