<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeKamar extends Model
{
    use HasFactory;

    protected $table = 'tipe_kamars';
    protected $primaryKey = 'id_tipe_kamar';

    protected $fillable = [
        'nama_tipe_kamar',
        'harga_per_malam',
        'deskripsi', // Menambahkan 'deskripsi' di sini
        'foto_url',
    ];

    protected $casts = [
        'harga_per_malam' => 'float',
    ];

    public function kamars()
    {
        return $this->hasMany(Kamar::class, 'id_tipe_kamar', 'id_tipe_kamar');
    }

    // NEW: Relasi baru untuk fasilitas default yang termasuk dalam tipe kamar
    public function fasilitas()
    {
    // Relasi Many-to-Many ke fasilitas (Fasilitas Dasar)
        return $this->belongsToMany(Fasilitas::class, 'tipe_kamar_fasilitas', 'id_tipe_kamar', 'id_fasilitas')
                    ->withTimestamps();
    }
}
