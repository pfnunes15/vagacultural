<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'phone', 'avatar_path', 'locale', 'nationality', 'bio'];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Full display name, composed from first and last name.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->first_name . ' ' . $this->last_name));
    }

    /** @return HasMany<UserRoleAssignment, $this> */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    /**
     * The promoter profile owned by this account, if it is a promoter.
     *
     * @return HasOne<Promoter, $this>
     */
    public function promoterProfile(): HasOne
    {
        return $this->hasOne(Promoter::class);
    }

    /**
     * The organization managed by this account, if it is an organization.
     *
     * @return HasOne<Organization, $this>
     */
    public function ownedOrganization(): HasOne
    {
        return $this->hasOne(Organization::class);
    }

    /** @return HasMany<PromoterRequest, $this> */
    public function promoterRequests(): HasMany
    {
        return $this->hasMany(PromoterRequest::class);
    }

    /** @return HasMany<Follow, $this> */
    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return HasMany<AgendaItem, $this> */
    public function agendaItems(): HasMany
    {
        return $this->hasMany(AgendaItem::class);
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->roles->contains(
            fn (UserRoleAssignment $assignment): bool => $assignment->role === $role,
        );
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isOrganization(): bool
    {
        return $this->hasRole(UserRole::Organization);
    }

    public function isPromoter(): bool
    {
        return $this->hasRole(UserRole::Promoter);
    }
}
