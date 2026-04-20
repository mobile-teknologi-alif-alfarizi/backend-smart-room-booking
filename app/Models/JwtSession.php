<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JwtSession extends Model
{
    protected $table = 'jwt_sessions';

    protected $fillable = [
        'user_id',
        'jti',
        'last_activity',
        'expires_at',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Check if session is still active (within 12 hours of inactivity)
     */
    public function isActive(): bool
    {
        $inactivityLimit = now()->subHours(12);
        return $this->last_activity->isAfter($inactivityLimit);
    }
}
