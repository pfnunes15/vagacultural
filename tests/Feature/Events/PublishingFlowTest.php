<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\Events\EventWorkflow;

function makeUserWithRole(UserRole $role): User
{
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => $role->value]);

    return $user;
}

function submitSample(Promoter $promoter, User $submitter): Event
{
    return app(EventWorkflow::class)->submit(
        $promoter,
        ['title' => 'Sessão de Jazz', 'is_free' => true],
        [],
        [['starts_at' => now()->addWeek()]],
        $submitter,
    );
}

it('publishes immediately for a trusted (auto_publish) promoter', function (): void {
    $user = makeUserWithRole(UserRole::Promoter);
    $promoter = Promoter::factory()->create(['user_id' => $user->id, 'auto_publish' => true]);

    $event = submitSample($promoter, $user);

    expect($event->status)->toBe(EventStatus::Published)
        ->and($event->published_at)->not->toBeNull()
        ->and($event->occurrences()->count())->toBe(1);
});

it('queues as pending for an untrusted promoter', function (): void {
    $user = makeUserWithRole(UserRole::Promoter);
    $promoter = Promoter::factory()->create(['user_id' => $user->id, 'auto_publish' => false]);

    $event = submitSample($promoter, $user);

    expect($event->status)->toBe(EventStatus::Pending)
        ->and($event->published_at)->toBeNull();
});

it('lets an admin approve a pending event', function (): void {
    $promoter = Promoter::factory()->create(['auto_publish' => false]);
    $event = Event::factory()->create(['promoter_id' => $promoter->id, 'status' => EventStatus::Pending]);

    app(EventWorkflow::class)->approve($event->refresh());

    expect($event->refresh()->status)->toBe(EventStatus::Published)
        ->and($event->published_at)->not->toBeNull();
});

it('authorizes update: owner yes, stranger no, managing org yes, admin yes', function (): void {
    $ownerUser = makeUserWithRole(UserRole::Promoter);
    $promoter = Promoter::factory()->create(['user_id' => $ownerUser->id]);
    $event = Event::factory()->create(['promoter_id' => $promoter->id]);

    $stranger = makeUserWithRole(UserRole::Promoter);
    $admin = makeUserWithRole(UserRole::Admin);

    $orgUser = makeUserWithRole(UserRole::Organization);
    $org = Organization::factory()->create(['user_id' => $orgUser->id]);
    $managed = Promoter::factory()->create(['organization_id' => $org->id]);
    $managedEvent = Event::factory()->create(['promoter_id' => $managed->id]);

    expect($ownerUser->can('update', $event))->toBeTrue()
        ->and($stranger->can('update', $event))->toBeFalse()
        ->and($admin->can('update', $event))->toBeTrue()
        ->and($orgUser->can('update', $managedEvent))->toBeTrue()
        ->and($orgUser->can('update', $event))->toBeFalse();
});

it('creates an event through the promoter dashboard and reports published state', function (): void {
    $user = makeUserWithRole(UserRole::Promoter);
    $promoter = Promoter::factory()->create(['user_id' => $user->id, 'auto_publish' => true]);

    $this->actingAs($user)->post('/painel/eventos', [
        'promoter_id' => $promoter->id,
        'title' => 'Concerto ao Pôr do Sol',
        'is_free' => '1',
        'occurrences' => [['starts_at' => now()->addDays(5)->format('Y-m-d H:i:s')]],
    ])->assertRedirect(route('painel.eventos.index'))->assertSessionHas('status');

    $this->assertDatabaseHas('events', ['title' => 'Concerto ao Pôr do Sol', 'status' => 'published']);
});

it('stops a promoter from posting as a promoter they do not control', function (): void {
    $user = makeUserWithRole(UserRole::Promoter);
    Promoter::factory()->create(['user_id' => $user->id]);
    $other = Promoter::factory()->create();

    $this->actingAs($user)->post('/painel/eventos', [
        'promoter_id' => $other->id,
        'title' => 'Intruso',
        'is_free' => '1',
        'occurrences' => [['starts_at' => now()->addDay()->format('Y-m-d H:i:s')]],
    ])->assertSessionHasErrors('promoter_id');

    $this->assertDatabaseMissing('events', ['title' => 'Intruso']);
});

it('blocks a normal user from the promoter dashboard', function (): void {
    $user = makeUserWithRole(UserRole::User);

    $this->actingAs($user)->get('/painel/eventos')->assertForbidden();
});

it('lets an admin approve a pending event over HTTP', function (): void {
    $admin = makeUserWithRole(UserRole::Admin);
    $event = Event::factory()->create(['status' => EventStatus::Pending]);

    $this->actingAs($admin)->post(route('admin.eventos.approve', $event))->assertRedirect();

    expect($event->refresh()->status)->toBe(EventStatus::Published);
});

it('lets an organization create events only when it has an associated promoter', function (): void {
    $orgUser = makeUserWithRole(UserRole::Organization);
    $org = Organization::factory()->create(['user_id' => $orgUser->id]);

    // No promoter yet -> cannot create.
    expect($orgUser->can('create', Event::class))->toBeFalse();
    $this->actingAs($orgUser)->get('/painel/eventos/novo')->assertForbidden();

    // Associate a promoter -> can create.
    Promoter::factory()->create(['organization_id' => $org->id]);
    expect($orgUser->fresh()->can('create', Event::class))->toBeTrue();
    $this->actingAs($orgUser->fresh())->get('/painel/eventos/novo')->assertOk();
});

it('lets a promoter with a profile create but not one without', function (): void {
    $withProfile = makeUserWithRole(UserRole::Promoter);
    Promoter::factory()->create(['user_id' => $withProfile->id]);

    $withoutProfile = makeUserWithRole(UserRole::Promoter);

    expect($withProfile->can('create', Event::class))->toBeTrue()
        ->and($withoutProfile->can('create', Event::class))->toBeFalse();
});
