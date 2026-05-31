<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $fillable = ['kampus_id', 'nama_ruangan'];

    /**
     * Get the kampus that owns the ruangan.
     */
    public function kampus(): BelongsTo
    {
        return $this->belongsTo(Kampus::class);
    }

    /**
     * Get all bookings for the ruangan.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
