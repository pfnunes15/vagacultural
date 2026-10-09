<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Support\Facades\Route;

it('registers a new user with the default role and returns a token', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Maria',
        'last_name' => 'Silva',
        'email' => 'maria@example.pt',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
        'device_name' => 'iPhone de Maria',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'maria@example.pt')
        ->assertJsonPath('user.roles', ['user'])
        ->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles'], 'token']);

    $this->assertDatabaseHas('users', ['email' => 'maria@example.pt']);
    $user = User::where('email', 'maria@example.pt')->first();
    expect($user->tokens()->count())->toBe(1)
        ->and($user->hasRole(UserRole::User))->toBeTrue();
});

it('rejects registration with a duplicate email', function (): void {
    User::factory()->create(['email' => 'taken@example.pt']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Outro',
        'email' => 'taken@example.pt',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('logs in with valid credentials and issues a token', function (): void {
    User::factory()->create(['email' => 'joao@example.pt', 'password' => 'password1234']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'joao@example.pt',
        'password' => 'password1234',
        'device_name' => 'Android',
    ])->assertOk()->assertJsonStructure(['user', 'token']);
});

it('rejects login with wrong credentials', function (): void {
    User::factory()->create(['email' => 'joao@example.pt', 'password' => 'password1234']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'joao@example.pt',
        'password' => 'wrong-password',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

it('returns the current user on /me and requires authentication', function (): void {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

it('revokes the current token on logout', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('blocks a non-admin from a role-guarded route', function (): void {
    Route::middleware(['auth:sanctum', 'role:admin'])->get('/test-admin-only', fn () => response()->json(['ok' => true]));

    $user = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => UserRole::User->value]);

    $this->withToken($user->createToken('t')->plainTextToken)->getJson('/test-admin-only')->assertForbidden();
});

it('allows an admin through a role-guarded route', function (): void {
    Route::middleware(['auth:sanctum', 'role:admin'])->get('/test-admin-only', fn () => response()->json(['ok' => true]));

    $admin = User::factory()->create();
    UserRoleAssignment::create(['user_id' => $admin->id, 'role' => UserRole::Admin->value]);

    $this->withToken($admin->createToken('t')->plainTextToken)->getJson('/test-admin-only')->assertOk();
});
