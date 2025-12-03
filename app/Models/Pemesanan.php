<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanans';
    protected $primaryKey = 'id_pemesanan';

    protected $fillable = [
        'user_id',
        'kamar_id',
        'check_in_date',
        'check_out_date',
        'jumlah_tamu',
        'total_harga',
        'status_pemesanan',
    ];

    protected $casts = [
        'check_in_date'  => 'date',
        'check_out_date' => 'date',
        'total_harga'    => 'decimal:2',
    ];

    /**
     * Relasi ke User (Pemesan).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Kamar.
     */
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id', 'id_kamar');
    }

    /**
     * Relasi ke Fasilitas (Pivot).
     */
    public function fasilitas(): BelongsToMany
    {
        return $this->belongsToMany(Fasilitas::class, 'pemesanan_fasilitas', 'id_pemesanan', 'id_fasilitas')
            ->withPivot('jumlah', 'total_harga_fasilitas')
            ->withTimestamps();
    }
}
