<?php

declare(strict_types=1);

namespace App\Services\Push\Drivers;

use App\Contracts\PushSender;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging (HTTP v1) driver. Handles both Android and iOS
 * (APNs through FCM). Requires a service-account access token; wiring the
 * OAuth token provider is a deploy-time step.
 */
class FcmPushSender implements PushSender
{
    public function toUser(User $user, string $title, string $body, array $data = []): int
    {
        $projectId = (string) config('push.fcm.project_id');
        $accessToken = (string) config('push.fcm.access_token');

        if ($projectId === '' || $accessToken === '') {
            Log::warning('[push] FCM not configured; skipping send', ['user_id' => $user->id]);

            return 0;
        }

        $sent = 0;
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach ($user->deviceTokens()->get() as $device) {
            $response = Http::withToken($accessToken)->post($url, [
                'message' => [
                    'token' => $device->token,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data' => array_map('strval', $data),
                ],
            ]);

            if ($response->successful()) {
                $sent++;
            } elseif ($response->status() === 404) {
                // Token no longer valid — prune it.
                $device->delete();
            }
        }

        return $sent;
    }
}
