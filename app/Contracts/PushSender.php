<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

/**
 * Sends push notifications to a user's registered devices.
 *
 * Implementations target FCM/APNs; a log driver is used in dev and tests so
 * the feature works end-to-end without external credentials.
 */
interface PushSender
{
    /**
     * @param  array<string, mixed>  $data  optional structured payload (e.g. deep link)
     * @return int number of devices the message was dispatched to
     */
    public function toUser(User $user, string $title, string $body, array $data = []): int;
}
