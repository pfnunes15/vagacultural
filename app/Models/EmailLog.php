<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $sent_at
 */
class EmailLog extends Model
{
    protected $fillable = [
        'user_id', 'type', 'recipient', 'subject', 'status', 'sent_by', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
