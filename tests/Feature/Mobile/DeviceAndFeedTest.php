<?php

declare(strict_types=1);

use App\Contracts\PushSender;
use App\Models\DeviceToken;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Follow;
use App\Models\Promoter;
use App\Models\User;
use App\Services\Events\EventWorkflow;

function apiUser(): array
{
    $user = User::factory()->create();

    return [$user, $user->createToken('app')->plainTextToken];
}

it('registers a device token', function (): void {
    [$user, $token] = apiUser();

    $this->withToken($token)->postJson('/api/v1/me/devices', [
        'token' => 'fcm-token-abc',
        'platform' => 'android',
        'locale' => 'pt',
    ])->assertCreated()->assertJsonPath('platform', 'android');

    $this->assertDatabaseHas('device_tokens', ['user_id' => $user->id, 'token' => 'fcm-token-abc']);
});

it('is idempotent on the device token (updateOrCreate)', function (): void {
    [$user, $token] = apiUser();

    $this->withToken($token)->postJson('/api/v1/me/devices', ['token' => 't1', 'platform' => 'ios'])->assertCreated();
    $this->withToken($token)->postJson('/api/v1/me/devices', ['token' => 't1', 'platform' => 'ios'])->assertCreated();

    expect(DeviceToken::where('token', 't1')->count())->toBe(1);
});

it('unregisters a device token', function (): void {
    [$user, $token] = apiUser();
    DeviceToken::create(['user_id' => $user->id, 'platform' => 'ios', 'token' => 'to-remove']);

    $this->withToken($token)->deleteJson('/api/v1/me/devices/to-remove')->assertOk();
    $this->assertDatabaseMissing('device_tokens', ['token' => 'to-remove']);
});

it('requires a token for device + feed endpoints', function (): void {
    $this->postJson('/api/v1/me/devices', [])->assertUnauthorized();
    $this->getJson('/api/v1/me/feed')->assertUnauthorized();
});

it('returns an aggregated home feed', function (): void {
    [$user, $token] = apiUser();
    $featured = Event::factory()->published()->create(['is_featured' => true]);
    EventOccurrence::factory()->create(['event_id' => $featured->id, 'starts_at' => now()->addDays(2)]);

    $this->withToken($token)->getJson('/api/v1/me/feed')
        ->assertOk()
        ->assertJsonStructure(['data' => [['key', 'title', 'events']]]);
});

it('pushes to followers when a followed promoter publishes an event', function (): void {
    $spy = Mockery::spy(PushSender::class);
    app()->instance(PushSender::class, $spy);

    $follower = User::factory()->create();
    DeviceToken::create(['user_id' => $follower->id, 'platform' => 'android', 'token' => 'dev-1']);

    $promoterUser = User::factory()->create();
    $promoter = Promoter::factory()->create(['user_id' => $promoterUser->id, 'auto_publish' => true]);
    Follow::create(['user_id' => $follower->id, 'followable_type' => $promoter->getMorphClass(), 'followable_id' => $promoter->id]);

    app(EventWorkflow::class)->submit(
        $promoter,
        ['title' => ['pt' => 'Novo Concerto']],
        [],
        [['starts_at' => now()->addWeek()]],
        $promoterUser,
    );

    $spy->shouldHaveReceived('toUser')->once();
});

it('does not push when the submission stays pending', function (): void {
    $spy = Mockery::spy(PushSender::class);
    app()->instance(PushSender::class, $spy);

    $follower = User::factory()->create();
    DeviceToken::create(['user_id' => $follower->id, 'platform' => 'ios', 'token' => 'dev-2']);
    $promoter = Promoter::factory()->create(['auto_publish' => false]);
    Follow::create(['user_id' => $follower->id, 'followable_type' => $promoter->getMorphClass(), 'followable_id' => $promoter->id]);

    app(EventWorkflow::class)->submit($promoter, ['title' => ['pt' => 'Pendente']], [], [['starts_at' => now()->addWeek()]], $promoter->user ?? User::factory()->create());

    $spy->shouldNotHaveReceived('toUser');
});
