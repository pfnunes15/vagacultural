<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Favorite;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Notifications\PromoterNudge;
use App\Notifications\WeeklyDigest;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

function upcomingPublished(): Event
{
    $e = Event::factory()->published()->create();
    EventOccurrence::factory()->create(['event_id' => $e->id, 'starts_at' => now()->addDays(3)]);

    return $e;
}

it('sends the weekly digest to an opted-in verified user with recommendations', function (): void {
    Notification::fake();
    $user = User::factory()->create(['marketing_emails' => true, 'email_verified_at' => now()]);
    $cat = Category::factory()->create();
    $event = upcomingPublished();
    $event->categories()->attach($cat, ['is_primary' => true]);
    Favorite::create(['user_id' => $user->id, 'favoritable_type' => $cat->getMorphClass(), 'favoritable_id' => $cat->id]);

    $this->artisan('mail:weekly-digest')->assertSuccessful();

    Notification::assertSentTo($user, WeeklyDigest::class);
    $this->assertDatabaseHas('email_logs', ['user_id' => $user->id, 'type' => 'weekly_digest']);
});

it('does not email users who opted out or are unverified', function (): void {
    Notification::fake();
    upcomingPublished();
    $optedOut = User::factory()->create(['marketing_emails' => false, 'email_verified_at' => now()]);
    $unverified = User::factory()->create(['marketing_emails' => true, 'email_verified_at' => null]);

    $this->artisan('mail:weekly-digest')->assertSuccessful();

    Notification::assertNotSentTo($optedOut, WeeklyDigest::class);
    Notification::assertNotSentTo($unverified, WeeklyDigest::class);
});

it('nudges an active promoter with no upcoming events', function (): void {
    Notification::fake();
    $user = User::factory()->create(['marketing_emails' => true]);
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => 'promoter']);
    Promoter::factory()->create(['user_id' => $user->id, 'is_active' => true]);

    $this->artisan('mail:promoter-nudge')->assertSuccessful();

    Notification::assertSentTo($user, PromoterNudge::class);
    $this->assertDatabaseHas('email_logs', ['user_id' => $user->id, 'type' => 'promoter_nudge']);
});

it('does not nudge a promoter who already has upcoming events', function (): void {
    Notification::fake();
    $user = User::factory()->create(['marketing_emails' => true]);
    UserRoleAssignment::create(['user_id' => $user->id, 'role' => 'promoter']);
    $promoter = Promoter::factory()->create(['user_id' => $user->id, 'is_active' => true]);
    $event = Event::factory()->published()->create(['promoter_id' => $promoter->id]);
    EventOccurrence::factory()->create(['event_id' => $event->id, 'starts_at' => now()->addDay()]);

    $this->artisan('mail:promoter-nudge')->assertSuccessful();

    Notification::assertNotSentTo($user, PromoterNudge::class);
});

it('unsubscribes via a signed link', function (): void {
    $user = User::factory()->create(['marketing_emails' => true]);
    $url = URL::signedRoute('unsubscribe', ['user' => $user->id]);

    $this->get($url)->assertRedirect(route('events.index'));
    expect($user->fresh()->marketing_emails)->toBeFalse();
});

it('rejects an unsigned unsubscribe link', function (): void {
    $user = User::factory()->create(['marketing_emails' => true]);
    $this->get("/unsubscribe/{$user->id}")->assertForbidden();
});
