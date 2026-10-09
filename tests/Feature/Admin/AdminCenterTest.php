<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\EmailLog;
use App\Models\Event;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;

function admin(): User
{
    $u = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $u->id, 'role' => UserRole::Admin->value]);

    return $u;
}

function roleUser(UserRole $role): User
{
    $u = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $u->id, 'role' => $role->value]);

    return $u;
}

it('blocks non-admins from the admin center', function (): void {
    $this->actingAs(roleUser(UserRole::User))->get('/admin')->assertForbidden();
    $this->actingAs(roleUser(UserRole::Promoter))->get('/admin/utilizadores')->assertForbidden();
});

it('opens the dashboard, users list and system status for an admin', function (): void {
    $admin = admin();
    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/utilizadores')->assertOk();
    $this->actingAs($admin)->get('/admin/sistema')->assertOk()->assertSee('Base de dados');
});

it('assigns roles to a user', function (): void {
    $admin = admin();
    $target = roleUser(UserRole::User);

    $this->actingAs($admin)->post(route('admin.users.roles', $target), [
        'roles' => [UserRole::User->value, UserRole::Promoter->value],
    ])->assertRedirect();

    expect($target->fresh()->isPromoter())->toBeTrue();
});

it('prevents an admin from removing their own admin role', function (): void {
    $admin = admin();

    $this->actingAs($admin)->post(route('admin.users.roles', $admin), [
        'roles' => [UserRole::User->value],
    ])->assertSessionHasErrors('roles');

    expect($admin->fresh()->isAdmin())->toBeTrue();
});

it('resends a registration email and records it in the log', function (): void {
    $admin = admin();
    $target = roleUser(UserRole::User);

    $this->actingAs($admin)->post(route('admin.users.resend', $target))->assertRedirect();

    expect(EmailLog::where('user_id', $target->id)->where('type', 'registration_verification')->exists())->toBeTrue();
});

it('toggles a promoter trust flag', function (): void {
    $admin = admin();
    $promoterUser = roleUser(UserRole::Promoter);
    $promoter = Promoter::factory()->create(['user_id' => $promoterUser->id, 'auto_publish' => false]);

    $this->actingAs($admin)->post(route('admin.users.trust', $promoterUser))->assertRedirect();

    expect($promoter->fresh()->auto_publish)->toBeTrue();
});

it('lets an admin impersonate a promoter and return', function (): void {
    $admin = admin();
    $promoterUser = roleUser(UserRole::Promoter);
    Promoter::factory()->create(['user_id' => $promoterUser->id]);

    $this->actingAs($admin)
        ->post(route('admin.users.impersonate', $promoterUser))
        ->assertRedirect(route('painel.eventos.index'));

    expect(auth()->id())->toBe($promoterUser->id)
        ->and(session()->has('impersonator_id'))->toBeTrue();

    $this->post(route('impersonate.stop'))->assertRedirect(route('admin.users.index'));
    expect(auth()->id())->toBe($admin->id)
        ->and(session()->has('impersonator_id'))->toBeFalse();
});

it('refuses to impersonate another admin', function (): void {
    $admin = admin();
    $other = admin();

    $this->actingAs($admin)
        ->post(route('admin.users.impersonate', $other))
        ->assertSessionHasErrors('impersonate');

    expect(auth()->id())->toBe($admin->id);
});

it('approves pending events from the admin area', function (): void {
    $admin = admin();
    $event = Event::factory()->create(['status' => EventStatus::Pending]);

    $this->actingAs($admin)->get('/admin/eventos/pendentes')->assertOk()->assertSee($event->title);
    $this->actingAs($admin)->post(route('admin.eventos.approve', $event))->assertRedirect();

    expect($event->fresh()->status)->toBe(EventStatus::Published);
});
