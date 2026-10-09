<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

it('registers a user through the web form and logs them in', function (): void {
    $this->post('/register', [
        'name' => 'Rui Gomes',
        'email' => 'rui@example.pt',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ])->assertRedirect('/events');

    $this->assertAuthenticated();
    $user = User::where('email', 'rui@example.pt')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole(UserRole::User))->toBeTrue();
});

it('logs a user out', function (): void {
    $this->actingAs(User::factory()->create())
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
