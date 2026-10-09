<?php

declare(strict_types=1);

namespace App\Services\Engagement;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Tag;
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

    /**
     * Replace the user's favorite categories with the given set.
     *
     * @param  list<int>  $categoryIds
     */
    public function syncCategories(User $user, array $categoryIds): void
    {
        $type = (new Category)->getMorphClass();

        $user->favorites()->where('favoritable_type', $type)->delete();

        foreach (array_unique($categoryIds) as $id) {
            $user->favorites()->create(['favoritable_type' => $type, 'favoritable_id' => $id]);
        }
    }

    /**
     * Replace the user's favorite tags with the given set.
     *
     * @param  list<int>  $tagIds
     */
    public function syncTags(User $user, array $tagIds): void
    {
        $type = (new Tag)->getMorphClass();

        $user->favorites()->where('favoritable_type', $type)->delete();

        foreach (array_unique($tagIds) as $id) {
            $user->favorites()->create(['favoritable_type' => $type, 'favoritable_id' => $id]);
        }
    }

    public function has(User $user, Model $favoritable): bool
    {
        return $user->favorites()
            ->where('favoritable_type', $favoritable->getMorphClass())
            ->where('favoritable_id', $favoritable->getKey())
            ->exists();
    }
}
