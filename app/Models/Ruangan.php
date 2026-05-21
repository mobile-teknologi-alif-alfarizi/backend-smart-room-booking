<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $fillable = ['kampus_id', 'nama_ruangan'];

    /**
     * Get the kampus that owns the ruangan.
     */
    public function kampus()
    {
        return $this->belongsTo(Kampus::class);
    }
}
