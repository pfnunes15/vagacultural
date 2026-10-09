<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'website', 'email', 'phone', 'logo_path', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * The account (role: organization) that manages this organization.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<Promoter, $this> */
    public function promoters(): HasMany
    {
        return $this->hasMany(Promoter::class);
    }

    /**
     * Events created by any of this organization's promoters.
     *
     * @return HasManyThrough<Event, Promoter, $this>
     */
    public function events(): HasManyThrough
    {
        return $this->hasManyThrough(Event::class, Promoter::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
