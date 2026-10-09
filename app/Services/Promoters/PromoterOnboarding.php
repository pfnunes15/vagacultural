<?php

declare(strict_types=1);

namespace App\Services\Promoters;

use App\Enums\PromoterRequestStatus;
use App\Enums\UserRole;
use App\Models\EmailLog;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\PromoterRequest;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Notifications\OrganizationActivated;
use App\Notifications\PromoterActivated;
use App\Notifications\PromoterRejected;
use App\Support\Slug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Onboarding of new promoters and organizations: a registered user applies,
 * and an admin decides whether to activate them as a promoter or as an
 * organization (or rejects). The applicant may hint a preferred type.
 */
class PromoterOnboarding
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function requestFor(User $user, array $data): PromoterRequest
    {
        if ($user->isPromoter() || $user->isOrganization()) {
            throw new RuntimeException('Já tens um perfil de promotor ou organização.');
        }

        if ($this->hasPendingRequest($user)) {
            throw new RuntimeException('Já tens uma candidatura pendente.');
        }

        $type = $data['requested_type'] ?? null;

        return $user->promoterRequests()->create([
            'proposed_name' => $data['proposed_name'],
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'message' => $data['message'] ?? null,
            'requested_type' => in_array($type, ['promoter', 'organization'], true) ? $type : null,
            'status' => PromoterRequestStatus::Pending->value,
        ]);
    }

    public function hasPendingRequest(User $user): bool
    {
        return $user->promoterRequests()
            ->where('status', PromoterRequestStatus::Pending->value)
            ->exists();
    }

    /**
     * Approve a pending request: create the promoter, grant the role, link it back.
     */
    public function approve(PromoterRequest $request, User $admin): Promoter
    {
        return DB::transaction(function () use ($request, $admin): Promoter {
            $promoter = Promoter::create([
                'user_id' => $request->user_id,
                'name' => $request->proposed_name,
                'slug' => $this->uniqueSlug($request->proposed_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'is_verified' => true,
                'auto_publish' => false,
                'is_active' => true,
            ]);

            UserRoleAssignment::firstOrCreate([
                'user_id' => $request->user_id,
                'role' => UserRole::Promoter->value,
            ]);

            $request->update([
                'status' => PromoterRequestStatus::Approved->value,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'created_promoter_id' => $promoter->id,
            ]);

            $request->user?->notify(new PromoterActivated($promoter));

            EmailLog::create([
                'user_id' => $request->user_id,
                'type' => 'promoter_activation',
                'recipient' => (string) ($request->user->email ?? $request->email),
                'subject' => 'Perfil de promotor ativado',
                'status' => 'sent',
                'sent_by' => $admin->id,
                'sent_at' => now(),
            ]);

            return $promoter;
        });
    }

    /**
     * Approve a pending request as an ORGANIZATION: create the organization
     * owned by the applicant, grant the role, link it back and notify.
     */
    public function approveAsOrganization(PromoterRequest $request, User $admin): Organization
    {
        return DB::transaction(function () use ($request, $admin): Organization {
            $organization = Organization::create([
                'user_id' => $request->user_id,
                'name' => $request->proposed_name,
                'slug' => Slug::unique(Organization::class, $request->proposed_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'is_active' => true,
            ]);

            UserRoleAssignment::firstOrCreate([
                'user_id' => $request->user_id,
                'role' => UserRole::Organization->value,
            ]);

            $request->update([
                'status' => PromoterRequestStatus::Approved->value,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'created_organization_id' => $organization->id,
            ]);

            $request->user?->notify(new OrganizationActivated($organization));

            EmailLog::create([
                'user_id' => $request->user_id,
                'type' => 'organization_activation',
                'recipient' => (string) ($request->user->email ?? $request->email),
                'subject' => 'Organização ativada',
                'status' => 'sent',
                'sent_by' => $admin->id,
                'sent_at' => now(),
            ]);

            return $organization;
        });
    }

    public function reject(PromoterRequest $request, User $admin, ?string $notes = null): void
    {
        $request->update([
            'status' => PromoterRequestStatus::Rejected->value,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $request->user?->notify(new PromoterRejected($notes));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Promoter::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
