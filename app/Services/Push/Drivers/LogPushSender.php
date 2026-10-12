<?php

declare(strict_types=1);

namespace App\Services\Push\Drivers;

use App\Contracts\PushSender;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Default driver: records what would be sent. Keeps the whole push pipeline
 * functional and testable without FCM/APNs credentials.
 */
class LogPushSender implements PushSender
{
    public function toUser(User $user, string $title, string $body, array $data = []): int
    {
        $tokens = $user->deviceTokens()->get();

        foreach ($tokens as $device) {
            Log::channel(config('push.log_channel', 'stack'))->info('[push] would send', [
                'user_id' => $user->id,
                'platform' => $device->platform->value,
                'token' => substr($device->token, 0, 12) . '…',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        }

        return $tokens->count();
    }
}
