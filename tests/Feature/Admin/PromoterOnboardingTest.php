<?php

declare(strict_types=1);

use App\Enums\PromoterRequestStatus;
use App\Enums\UserRole;
use App\Models\Promoter;
use App\Models\PromoterRequest;
use App\Models\User;
use App\Models\UserRoleAssignment;

function userWithRole(UserRole $role): User
{
    $u = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $u->id, 'role' => $role->value]);

    return $u;
}

it('lets a registered user apply to become a promoter', function (): void {
    $user = userWithRole(UserRole::User);

    $this->actingAs($user)->get('/promotor/candidatura')->assertOk();

    $this->actingAs($user)->post('/promotor/candidatura', [
        'proposed_name' => 'Associação Cultural do Norte',
        'message' => 'Organizamos concertos mensais.',
    ])->assertRedirect(route('events.index'));

    $this->assertDatabaseHas('promoter_requests', [
        'user_id' => $user->id,
        'proposed_name' => 'Associação Cultural do Norte',
        'status' => 'pending',
    ]);
});

it('blocks a second pending application', function (): void {
    $user = userWithRole(UserRole::User);
    $user->promoterRequests()->create(['proposed_name' => 'X', 'status' => PromoterRequestStatus::Pending->value]);

    $this->actingAs($user)->post('/promotor/candidatura', ['proposed_name' => 'Y'])
        ->assertSessionHasErrors('proposed_name');

    expect(PromoterRequest::where('user_id', $user->id)->count())->toBe(1);
});

it('redirects an existing promoter away from the application form', function (): void {
    $user = userWithRole(UserRole::Promoter);
    Promoter::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get('/promotor/candidatura')->assertRedirect(route('painel.eventos.index'));
});

it('provisions promoter profile and role when an admin approves', function (): void {
    $admin = userWithRole(UserRole::Admin);
    $applicant = userWithRole(UserRole::User);
    $request = $applicant->promoterRequests()->create([
        'proposed_name' => 'Teatro Novo',
        'status' => PromoterRequestStatus::Pending->value,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.promoters.requests.approve', $request))
        ->assertRedirect();

    $applicant->refresh();
    expect($applicant->isPromoter())->toBeTrue()
        ->and($applicant->promoterProfile)->not->toBeNull()
        ->and($applicant->promoterProfile->name)->toBe('Teatro Novo')
        ->and($applicant->promoterProfile->auto_publish)->toBeFalse();

    $request->refresh();
    expect($request->status)->toBe(PromoterRequestStatus::Approved)
        ->and($request->created_promoter_id)->toBe($applicant->promoterProfile->id)
        ->and($request->reviewed_by)->toBe($admin->id);
});

it('marks a request rejected with notes', function (): void {
    $admin = userWithRole(UserRole::Admin);
    $applicant = userWithRole(UserRole::User);
    $request = $applicant->promoterRequests()->create([
        'proposed_name' => 'Spam Co',
        'status' => PromoterRequestStatus::Pending->value,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.promoters.requests.reject', $request), ['notes' => 'Fora de âmbito'])
        ->assertRedirect();

    $request->refresh();
    expect($request->status)->toBe(PromoterRequestStatus::Rejected)
        ->and($request->review_notes)->toBe('Fora de âmbito')
        ->and($applicant->fresh()->isPromoter())->toBeFalse();
});

it('keeps the promoter requests queue admin-only', function (): void {
    $this->actingAs(userWithRole(UserRole::Promoter))->get('/admin/promotores/pedidos')->assertForbidden();
});
