<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'id_role'; // <<< PASTIKAN BARIS INI ADA
    public $incrementing = true; // Tambahkan ini jika primary key Anda auto-increment
    protected $fillable = ['nama_role'];
}