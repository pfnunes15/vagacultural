<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PromoterRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoterRequest extends Model
{
    protected $fillable = [
        'user_id', 'proposed_name', 'email', 'phone', 'website', 'message',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes', 'created_promoter_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PromoterRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<Promoter, $this> */
    public function promoter(): BelongsTo
    {
        return $this->belongsTo(Promoter::class, 'created_promoter_id');
    }
}
