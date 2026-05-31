<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kampus extends Model
{
    protected $table = 'kampus';

    protected $fillable = [
        'nama_kampus',
        'alamat',
    ];

    /**
     * Get all ruangan in this kampus.
     */
    public function ruangan(): HasMany
    {
        return $this->hasMany(Ruangan::class);
    }
}
