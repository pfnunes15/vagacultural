<?php

declare(strict_types=1);

namespace App\Services\Promoters;

use App\Models\Promoter;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolves which promoters a given account may act as when creating events.
 * - admin: every active promoter
 * - organization: the promoters it manages
 * - promoter: its own promoter profile
 */
class PromoterAccess
{
    /**
     * @return Collection<int, Promoter>
     */
    public function postableBy(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Promoter::query()->where('is_active', true)->orderBy('name')->get();
        }

        if ($user->isOrganization()) {
            $organization = $user->ownedOrganization;

            return $organization
                ? $organization->promoters()->where('is_active', true)->orderBy('name')->get()
                : collect();
        }

        if ($user->isPromoter()) {
            return $user->promoterProfile()->where('is_active', true)->get();
        }

        return collect();
    }

    public function canPostAs(User $user, Promoter $promoter): bool
    {
        return $this->postableBy($user)->contains('id', $promoter->id);
    }
}
