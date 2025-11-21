<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens; // Ini opsional, hanya jika Anda menggunakan Sanctum API Tokens

class User extends Authenticatable
{
    use HasFactory, Notifiable; // Tambahkan HasApiTokens jika Anda menggunakannya


    protected $table = 'users'; // Opsional jika nama tabel 'users'
    // protected $primaryKey = 'id'; // Opsional jika primary key 'id'

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_role', // Pastikan ini ada di fillable
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
        // Pastikan relasi ini sudah benar menggunakan 'id_role' sebagai foreign key
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    /**
     * Check if the user has a specific role.
     * Ini adalah helper method yang sangat berguna.
     */
    public function hasRole($roleName)
    {
        // Pastikan relasi role sudah dimuat sebelum mengakses namanya
        return $this->role && $this->role->nama_role == $roleName;
    }
}