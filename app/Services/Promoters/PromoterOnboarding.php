<?php

declare(strict_types=1);

namespace App\Services\Promoters;

use App\Enums\PromoterRequestStatus;
use App\Enums\UserRole;
use App\Models\EmailLog;
use App\Models\Promoter;
use App\Models\PromoterRequest;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Notifications\PromoterActivated;
use App\Notifications\PromoterRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Onboarding of new promoters: a registered user applies, an admin approves
 * (which provisions the promoter profile and grants the role) or rejects.
 */
class PromoterOnboarding
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function requestFor(User $user, array $data): PromoterRequest
    {
        if ($user->isPromoter()) {
            throw new RuntimeException('Já és promotor.');
        }

        if ($this->hasPendingRequest($user)) {
            throw new RuntimeException('Já tens uma candidatura a promotor pendente.');
        }

        return $user->promoterRequests()->create([
            'proposed_name' => $data['proposed_name'],
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'message' => $data['message'] ?? null,
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
