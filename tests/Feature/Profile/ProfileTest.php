<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('shows the profile page to a logged-in user', function (): void {
    $this->actingAs(User::factory()->create(['first_name' => 'Ana', 'last_name' => 'Costa']))
        ->get(route('my.profile'))->assertOk()->assertSee('Ana');
});

it('updates profile data and favorite categories', function (): void {
    $user = User::factory()->create();
    $catA = Category::factory()->create();
    $catB = Category::factory()->create();

    $this->actingAs($user)->put(route('my.profile.update'), [
        'first_name' => 'João',
        'last_name' => 'Pereira',
        'email' => $user->email,
        'nationality' => 'PT',
        'locale' => 'en',
        'categories' => [$catA->id, $catB->id],
    ])->assertRedirect();

    $user->refresh();
    expect($user->first_name)->toBe('João')
        ->and($user->nationality)->toBe('PT')
        ->and($user->locale)->toBe('en')
        ->and($user->name)->toBe('João Pereira');

    $this->assertDatabaseHas('favorites', [
        'user_id' => $user->id,
        'favoritable_type' => (new Category)->getMorphClass(),
        'favoritable_id' => $catA->id,
    ]);
});

it('resets email verification when the email changes', function (): void {
    $user = User::factory()->create(['email' => 'old@example.pt']);

    $this->actingAs($user)->put(route('my.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => 'new@example.pt',
    ])->assertRedirect();

    expect($user->fresh()->email)->toBe('new@example.pt')
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('changes the password with the correct current password', function (): void {
    $user = User::factory()->create(['password' => 'current-pass-123']);

    $this->actingAs($user)->put(route('my.profile.password'), [
        'current_password' => 'current-pass-123',
        'password' => 'brand-new-pass-456',
        'password_confirmation' => 'brand-new-pass-456',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Hash::check('brand-new-pass-456', $user->fresh()->password))->toBeTrue();
});

it('rejects a password change with a wrong current password', function (): void {
    $user = User::factory()->create(['password' => 'current-pass-123']);

    $this->actingAs($user)->put(route('my.profile.password'), [
        'current_password' => 'wrong',
        'password' => 'brand-new-pass-456',
        'password_confirmation' => 'brand-new-pass-456',
    ])->assertSessionHasErrors('current_password');
});

it('sends a password reset link and resets the password', function (): void {
    Notification::fake();
    $user = User::factory()->create(['email' => 'reset@example.pt']);

    $this->post(route('password.email'), ['email' => 'reset@example.pt'])->assertRedirect();

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
        $this->post(route('password.store'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'fresh-password-789',
            'password_confirmation' => 'fresh-password-789',
        ])->assertRedirect(route('login'));

        return true;
    });

    expect(Hash::check('fresh-password-789', $user->fresh()->password))->toBeTrue();
});

it('reads and updates the profile via the API', function (): void {
    $user = User::factory()->create(['first_name' => 'Rui']);
    $token = $user->createToken('app')->plainTextToken;

    $this->withToken($token)->getJson('/api/v1/me/profile')
        ->assertOk()->assertJsonPath('data.first_name', 'Rui');

    $this->withToken($token)->putJson('/api/v1/me/profile', ['first_name' => 'Rúben', 'nationality' => 'FR'])
        ->assertOk()->assertJsonPath('data.first_name', 'Rúben')->assertJsonPath('data.nationality', 'FR');
});

it('requires a token for the profile API', function (): void {
    $this->getJson('/api/v1/me/profile')->assertUnauthorized();
});
