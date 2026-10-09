<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PromoterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promoter extends Model
{
    /** @use HasFactory<PromoterFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'organization_id', 'name', 'slug', 'description', 'website',
        'email', 'phone', 'logo_path', 'is_verified', 'auto_publish', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'auto_publish' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The promoter's own account (role: promoter).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parent organization that manages this promoter, if any.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
