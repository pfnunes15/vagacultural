<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;

function promoterOwner(): array
{
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => UserRole::Promoter->value]);
    $promoter = Promoter::factory()->create(['user_id' => $user->id, 'auto_publish' => true]);

    return [$user, $promoter];
}

it('lets the owner open and submit the edit form', function (): void {
    [$user, $promoter] = promoterOwner();
    $category = Category::factory()->create();
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id, 'title' => 'Antigo']);
    EventOccurrence::factory()->create(['event_id' => $event->id]);

    $this->actingAs($user)->get(route('dashboard.events.edit', $event))->assertOk()->assertSee('Antigo');

    $this->actingAs($user)->put(route('dashboard.events.update', $event), [
        'title' => 'Novo Título',
        'categories' => [$category->id],
        'is_free' => '1',
        'tags' => 'jazz, noite',
        'occurrences' => [['starts_at' => now()->addDays(3)->format('Y-m-d H:i:s')]],
    ])->assertRedirect(route('dashboard.events.index'));

    $event->refresh();
    expect($event->title)->toBe('Novo Título')
        ->and($event->status)->toBe(EventStatus::Published) // status preserved on edit
        ->and($event->tags)->toHaveCount(2)
        ->and($event->occurrences()->count())->toBe(1);
});

it('forbids editing an event you do not own', function (): void {
    [, $promoter] = promoterOwner();
    $event = Event::factory()->create(['promoter_id' => $promoter->id]);

    $stranger = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $stranger->id, 'role' => UserRole::Promoter->value]);
    Promoter::factory()->create(['user_id' => $stranger->id]);

    $this->actingAs($stranger)->get(route('dashboard.events.edit', $event))->assertForbidden();
    $this->actingAs($stranger)->put(route('dashboard.events.update', $event), [
        'title' => 'Hack', 'categories' => [Category::factory()->create()->id], 'is_free' => '1',
        'occurrences' => [['starts_at' => now()->addDay()->format('Y-m-d H:i:s')]],
    ])->assertForbidden();
});

it('lets the managing organization edit its promoter event', function (): void {
    $orgUser = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $orgUser->id, 'role' => UserRole::Organization->value]);
    $org = Organization::factory()->create(['user_id' => $orgUser->id]);
    $promoter = Promoter::factory()->create(['organization_id' => $org->id]);
    $event = Event::factory()->create(['promoter_id' => $promoter->id]);

    $this->actingAs($orgUser)->get(route('dashboard.events.edit', $event))->assertOk();
});

it('soft-deletes an event the owner removes', function (): void {
    [$user, $promoter] = promoterOwner();
    $event = Event::factory()->create(['promoter_id' => $promoter->id]);

    $this->actingAs($user)->delete(route('dashboard.events.destroy', $event))->assertRedirect();

    $this->assertSoftDeleted('events', ['id' => $event->id]);
});

it('forbids deleting an event you do not own', function (): void {
    [, $promoter] = promoterOwner();
    $event = Event::factory()->create(['promoter_id' => $promoter->id]);

    $stranger = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $stranger->id, 'role' => UserRole::Promoter->value]);
    Promoter::factory()->create(['user_id' => $stranger->id]);

    $this->actingAs($stranger)->delete(route('dashboard.events.destroy', $event))->assertForbidden();
    $this->assertDatabaseHas('events', ['id' => $event->id, 'deleted_at' => null]);
});
