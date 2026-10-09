<?php

declare(strict_types=1);

namespace App\Services\Engagement;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Toggling and querying a user's favorites. Favorites are polymorphic, so the
 * same code works for events, categories, venues and promoters.
 */
class FavoriteService
{
    /**
     * @return bool the new state: true if now favorited, false if removed
     */
    public function toggle(User $user, Model $favoritable): bool
    {
        $existing = $user->favorites()
            ->where('favoritable_type', $favoritable->getMorphClass())
            ->where('favoritable_id', $favoritable->getKey())
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return false;
        }

        $favorite = new Favorite(['user_id' => $user->id]);
        $favorite->favoritable()->associate($favoritable);
        $favorite->save();

        return true;
    }

    public function has(User $user, Model $favoritable): bool
    {
        return $user->favorites()
            ->where('favoritable_type', $favoritable->getMorphClass())
            ->where('favoritable_id', $favoritable->getKey())
            ->exists();
    }
}
