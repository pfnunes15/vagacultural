<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Services\Promoters\PromoterAccess;

class EventPolicy
{
    public function __construct(private readonly PromoterAccess $access) {}

    /**
     * Events are always created on behalf of a promoter. Promoters create
     * through their own profile; organizations must be associated with at
     * least one promoter; admins may always create. An organization with no
     * associated promoter cannot create events.
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isPromoter() && ! $user->isOrganization()) {
            return false;
        }

        return $this->access->postableBy($user)->isNotEmpty();
    }

    /** Owner promoter, the managing organization, or an admin may manage an event. */
    public function update(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $promoter = $event->promoter;

        // The promoter's own account.
        if ($promoter->user_id !== null && $promoter->user_id === $user->id) {
            return true;
        }

        // The account that owns the organization managing this promoter.
        $organization = $promoter->organization;

        return $organization !== null
            && $organization->user_id !== null
            && $organization->user_id === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    /** Only admins approve or reject pending events. */
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
