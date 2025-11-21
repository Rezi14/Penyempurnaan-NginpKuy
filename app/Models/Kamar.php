<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamars';
    protected $primaryKey = 'id_kamar';

    protected $fillable = [
        'nomor_kamar',
        'id_tipe_kamar',
        'status_kamar',
        // 'deskripsi' dihapus dari sini
    ];

    protected $casts = [
        'status_kamar' => 'boolean',
    ];

    public function tipeKamar()
    {
        return $this->belongsTo(TipeKamar::class, 'id_tipe_kamar', 'id_tipe_kamar');
    }
}