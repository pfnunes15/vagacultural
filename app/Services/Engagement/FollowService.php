<?php

declare(strict_types=1);

namespace App\Services\Engagement;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Users follow promoters (and organizations) to keep up with their events.
 * Polymorphic so it extends to other followable entities later.
 */
class FollowService
{
    public function toggle(User $user, Model $followable): bool
    {
        $existing = Follow::query()
            ->where('user_id', $user->id)
            ->where('followable_type', $followable->getMorphClass())
            ->where('followable_id', $followable->getKey())
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        $follow = new Follow(['user_id' => $user->id]);
        $follow->followable()->associate($followable);
        $follow->save();

        return true;
    }

    public function isFollowing(User $user, Model $followable): bool
    {
        return Follow::query()
            ->where('user_id', $user->id)
            ->where('followable_type', $followable->getMorphClass())
            ->where('followable_id', $followable->getKey())
            ->exists();
    }
}
