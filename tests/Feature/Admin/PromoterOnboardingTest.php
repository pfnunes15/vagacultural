<?php

declare(strict_types=1);

use App\Enums\PromoterRequestStatus;
use App\Enums\UserRole;
use App\Models\Promoter;
use App\Models\PromoterRequest;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Notifications\OrganizationActivated;
use App\Notifications\PromoterActivated;
use App\Notifications\PromoterRejected;
use Illuminate\Support\Facades\Notification;

function userWithRole(UserRole $role): User
{
    $u = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $u->id, 'role' => $role->value]);

    return $u;
}

it('lets a registered user apply to become a promoter', function (): void {
    $user = userWithRole(UserRole::User);

    $this->actingAs($user)->get('/become-promoter')->assertOk();

    $this->actingAs($user)->post('/become-promoter', [
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

    $this->actingAs($user)->post('/become-promoter', ['proposed_name' => 'Y'])
        ->assertSessionHasErrors('proposed_name');

    expect(PromoterRequest::where('user_id', $user->id)->count())->toBe(1);
});

it('redirects an existing promoter away from the application form', function (): void {
    $user = userWithRole(UserRole::Promoter);
    Promoter::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get('/become-promoter')->assertRedirect(route('dashboard.events.index'));
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
    $this->actingAs(userWithRole(UserRole::Promoter))->get('/admin/promoters/requests')->assertForbidden();
});

it('notifies and logs an email when an admin activates a promoter', function (): void {
    Notification::fake();
    $admin = userWithRole(UserRole::Admin);
    $applicant = userWithRole(UserRole::User);
    $request = $applicant->promoterRequests()->create([
        'proposed_name' => 'Novo Promotor',
        'status' => PromoterRequestStatus::Pending->value,
    ]);

    $this->actingAs($admin)->post(route('admin.promoters.requests.approve', $request))->assertRedirect();

    Notification::assertSentTo($applicant, PromoterActivated::class);
    $this->assertDatabaseHas('email_logs', ['user_id' => $applicant->id, 'type' => 'promoter_activation']);
    expect($applicant->fresh()->promoterProfile->is_active)->toBeTrue();
});

it('notifies the applicant when an admin rejects', function (): void {
    Notification::fake();
    $admin = userWithRole(UserRole::Admin);
    $applicant = userWithRole(UserRole::User);
    $request = $applicant->promoterRequests()->create([
        'proposed_name' => 'Spam',
        'status' => PromoterRequestStatus::Pending->value,
    ]);

    $this->actingAs($admin)->post(route('admin.promoters.requests.reject', $request), ['notes' => 'Fora de âmbito'])->assertRedirect();

    Notification::assertSentTo($applicant, PromoterRejected::class);
});

it('lets an admin activate a request as an organization', function (): void {
    Notification::fake();
    $admin = userWithRole(UserRole::Admin);
    $applicant = userWithRole(UserRole::User);
    $request = $applicant->promoterRequests()->create([
        'proposed_name' => 'Casa da Música',
        'requested_type' => 'organization',
        'status' => PromoterRequestStatus::Pending->value,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.promoters.requests.approve-organization', $request))
        ->assertRedirect();

    $applicant->refresh();
    expect($applicant->isOrganization())->toBeTrue()
        ->and($applicant->ownedOrganization)->not->toBeNull()
        ->and($applicant->ownedOrganization->name)->toBe('Casa da Música');

    $request->refresh();
    expect($request->status)->toBe(PromoterRequestStatus::Approved)
        ->and($request->created_organization_id)->toBe($applicant->ownedOrganization->id);

    Notification::assertSentTo($applicant, OrganizationActivated::class);
    $this->assertDatabaseHas('email_logs', ['user_id' => $applicant->id, 'type' => 'organization_activation']);
});

it('stores the applicant type hint on the request', function (): void {
    $user = userWithRole(UserRole::User);

    $this->actingAs($user)->post('/become-promoter', [
        'proposed_name' => 'Entidade X',
        'requested_type' => 'organization',
    ])->assertRedirect();

    $this->assertDatabaseHas('promoter_requests', [
        'user_id' => $user->id,
        'requested_type' => 'organization',
    ]);
});
