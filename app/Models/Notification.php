<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'judul',
        'pesan',
        'keterangan',
        'jenis',
        'sumber',
        'dibaca_pada',
        'dikirim_pada',
    ];

    protected function casts(): array
    {
        return [
            'dibaca_pada' => 'datetime',
            'dikirim_pada' => 'datetime',
        ];
    }

    /**
     * Get user owner of notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get booking relation for notification.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
