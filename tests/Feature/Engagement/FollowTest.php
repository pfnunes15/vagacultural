<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets a logged-in user follow and unfollow a promoter (web)', function (): void {
    $user = User::factory()->create();
    $promoter = Promoter::factory()->create();

    $this->actingAs($user)->post(route('my.follow.promoter', $promoter))->assertRedirect();
    $this->assertDatabaseHas('follows', [
        'user_id' => $user->id,
        'followable_type' => $promoter->getMorphClass(),
        'followable_id' => $promoter->id,
    ]);
    expect($promoter->followers()->count())->toBe(1);

    $this->actingAs($user)->post(route('my.follow.promoter', $promoter))->assertRedirect();
    expect($promoter->followers()->count())->toBe(0);
});

it('requires login to follow (web)', function (): void {
    $promoter = Promoter::factory()->create();
    $this->post(route('my.follow.promoter', $promoter))->assertRedirect(route('login'));
});

it('toggles following via the API', function (): void {
    $token = User::factory()->create()->createToken('app')->plainTextToken;
    $promoter = Promoter::factory()->create();

    $this->withToken($token)->postJson("/api/v1/promoters/{$promoter->slug}/follow")
        ->assertOk()->assertJson(['following' => true]);
    $this->withToken($token)->postJson("/api/v1/promoters/{$promoter->slug}/follow")
        ->assertOk()->assertJson(['following' => false]);
});

it('shows the follow button and follower count on the public profile', function (): void {
    $user = User::factory()->create();
    $promoter = Promoter::factory()->create(['name' => 'Seguível']);

    $this->actingAs($user)->get(route('promoters.show', $promoter))
        ->assertOk()->assertSee('Seguir');
});

it('lets a promoter upload a logo through the dashboard profile', function (): void {
    Storage::fake('public');
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => 'promoter']);
    $promoter = Promoter::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('dashboard.profile'))->assertOk();

    $this->actingAs($user)->put(route('dashboard.profile.update'), [
        'name' => 'Promotor Com Logo',
        'description' => 'Bio',
        'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
    ])->assertRedirect();

    $promoter->refresh();
    expect($promoter->name)->toBe('Promotor Com Logo')
        ->and($promoter->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($promoter->logo_path);
});

it('lets an organization edit its dashboard profile', function (): void {
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => 'organization']);
    Organization::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('dashboard.profile'))->assertOk()->assertSee('organização');
});

it('404s the dashboard profile for a plain user', function (): void {
    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => 'user']);

    // plain users are not allowed in the dashboard group at all
    $this->actingAs($user)->get(route('dashboard.profile'))->assertForbidden();
});
