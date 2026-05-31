<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'ruangan_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'keperluan',
        'tipe_booking',
        'status',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ruangan that owns the booking.
     */
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * Get all notifications for the booking.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
