<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->nama_role == $roleName;
    }

    // --- TAMBAHAN BARU ---
    // Relasi ke tabel pemesanans
    public function pemesanans()
    {
        // Parameter ke-2 'user_id' harus sesuai dengan kolom foreign key di tabel pemesanans
        return $this->hasMany(Pemesanan::class, 'user_id', 'id');
    }
}
