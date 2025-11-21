<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'fasilitas';
    protected $primaryKey = 'id_fasilitas';

    protected $fillable = [
        'nama_fasilitas',
        'deskripsi',
        'biaya_tambahan',
    ];

    protected $casts = [
        'biaya_tambahan' => 'float',
    ];

    // Relasi baru untuk pemesanan
    public function pemesanans()
    {
        return $this->belongsToMany(Pemesanan::class, 'pemesanan_fasilitas', 'fasilitas_id', 'pemesanan_id')
                    ->withTimestamps();
    }
}
